<?php

namespace App\Service\Payment;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\PaymentChangeLog;
use App\Models\TrainingGroup;
use App\Service\PaymentAmountResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class PaymentListingService
{
    public function __construct(
        private Payment $payment,
        private PaymentAmountResolver $paymentAmountResolver
    ) {
    }

    public function filter($request, bool $deleted = false, $raw = false): array
    {
        $payments = $this->filterSelect($request->all(), $deleted, $raw)->get();
        $payments->setAppends(['check_payments']);

        if (! $raw) {
            return $this->generateTable($payments, $deleted);
        }

        return $this->generateData($payments, $deleted);
    }

    public function filterSelect(array $params, bool $deleted = false, bool $raw = false): Builder
    {
        $schoolId = data_get($params, 'school_id');
        $category = data_get($params, 'category');
        $year = data_get($params, 'year', now()->year);
        $month = data_get($params, 'month');
        $trainingGroupId = data_get($params, 'training_group_id', 0);
        $paymentStatus = $this->normalizePaymentStatus(data_get($params, 'status'));

        $query = $this->payment->query()
            ->where('school_id', $schoolId)
            ->with(['inscription' => fn ($query) => $query->with(['player'])->withTrashed()])
            ->when($deleted, fn ($q) => $q->withTrashed(), fn ($q) => $q->whereNull('payments.deleted_at'));

        $query
            ->addSelect([
                'category' => Inscription::withTrashed()
                    ->select('category')
                    ->whereColumn('inscriptions.id', 'payments.inscription_id')
                    ->where('year', $year)
                    ->take(1),
            ])
            ->where('year', $year)
            ->whereHas('inscription.player')
            ->whereHas('inscription', fn ($inscriptionQuery) => $inscriptionQuery->where('year', $year))
            ->when($trainingGroupId != 0, fn ($q) => $q->where('training_group_id', $trainingGroupId))
            ->when($category, fn ($q) => $q->whereHas('inscription', fn ($inscription) => $inscription
                ->where('year', $year)
                ->where('category', $category)))
            ->when($paymentStatus != null, function ($q) use ($paymentStatus, $month): void {
                if ($month && Payment::amountFieldFor((string) $month)) {
                    $q->where($month, $paymentStatus);
                    return;
                }

                $q->ByPaymentStatus($paymentStatus);
            });

        return $this->orderByCategory($query);
    }

    public function filterSelectRaw(array $params, bool $deleted = false): Builder
    {
        $schoolId = data_get($params, 'school_id');
        $category = data_get($params, 'category');
        $year = data_get($params, 'year', now()->year);
        $month = data_get($params, 'month');
        $trainingGroupId = data_get($params, 'training_group_id', 0);
        $paymentStatus = $this->normalizePaymentStatus(data_get($params, 'status'));

        $query = $this->payment->query()
            ->addSelect([
                'category' => Inscription::withTrashed()
                    ->select('category')
                    ->whereColumn('inscriptions.id', 'payments.inscription_id')
                    ->where('year', $year)
                    ->take(1),
            ])
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereHas('inscription.player')
            ->whereHas('inscription', fn ($inscriptionQuery) => $inscriptionQuery->where('year', $year))
            ->when($trainingGroupId != 0, fn ($q) => $q->where('training_group_id', $trainingGroupId))
            ->when($category, fn ($q) => $q->whereHas('inscription', fn ($inscription) => $inscription
                ->where('year', $year)
                ->where('category', $category)))
            ->when($paymentStatus != null, function ($q) use ($paymentStatus, $month): void {
                if ($month && Payment::amountFieldFor((string) $month)) {
                    $q->where($month, $paymentStatus);
                    return;
                }

                $q->ByPaymentStatus($paymentStatus);
            })
            ->when($deleted, fn ($q) => $q->withTrashed(), fn ($q) => $q->whereNull('payments.deleted_at'));

        return $this->orderByCategory($query)->orderBy('inscription_id', 'asc');
    }

    public function decoratePayment(Payment $payment): Payment
    {
        return $this->decoratePayments(new EloquentCollection([$payment]))->first();
    }

    private function generateResponse($rows, $count, $urlExportExcel, $urlExportPDF, array $extra = []): array
    {
        $response = [
            'rows' => $rows,
            'count' => $count,
            'url_export_excel' => $urlExportExcel,
            'url_export_pdf' => $urlExportPDF,
        ];

        return array_merge($response, $extra);
    }

    private function generateData($payments, $deleted): array
    {
        $payments = $this->decoratePayments($payments);
        $school = getSchool(auth()->user());
        $school->loadMissing('settingsValues');
        $year = (int) request()->input('year', now()->year);

        [$urlExportExcel, $urlExportPDF] = $this->generateLinks($payments, $deleted);

        $extra = [
            'inscription_amount' => $this->paymentAmountResolver->inscriptionAmountForSchool($school),
            'monthly_payment' => $this->paymentAmountResolver->monthlyAmountForSchool($school),
            'annuity' => $this->paymentAmountResolver->annuityAmountForSchool($school),
            'filter_options' => $this->filterOptions((int) $school->id, $year),
        ];

        return $this->generateResponse($payments, $payments->count(), $urlExportExcel, $urlExportPDF, $extra);
    }

    private function generateTable($payments, $deleted): array
    {
        $rows = '';
        $payments = $this->decoratePayments($payments);
        $payments->setAppends(['check_payments']);
        $school = getSchool(auth()->user());
        $school->loadMissing('settingsValues');
        $inscriptionAmount = $this->paymentAmountResolver->inscriptionAmountForSchool($school);
        $monthlyPayment = $this->paymentAmountResolver->monthlyAmountForSchool($school);
        $annuity = $this->paymentAmountResolver->annuityAmountForSchool($school);
        $nameFields = config('variables.KEY_INDEX_MONTHS');
        $nameFields[0] = 'enrollment';
        ksort($nameFields);

        foreach ($payments as $payment) {
            $rows .= View::make('templates.payments.row', [
                'payment' => $payment,
                'deleted' => $deleted,
                'front' => true,
                'nameFields' => $nameFields,
                'inscription_amount' => $inscriptionAmount,
                'monthly_payment' => $monthlyPayment,
                'annuity' => $annuity,
            ])->render();
        }

        [$urlExportExcel, $urlExportPDF] = $this->generateLinks($payments, $deleted);

        return $this->generateResponse($rows, $payments->count(), $urlExportExcel, $urlExportPDF);
    }

    private function generateLinks($payments, $deleted): array
    {
        $queryParams = request()->query();
        if ($deleted) {
            $queryParams['deleted'] = true;
        }

        if ($payments && ! request()->filled('training_group_id')) {
            $queryParams['training_group_id'] = 0;
        }

        ksort($queryParams);

        return [
            route('export.payments.excel', $queryParams),
            route('export.payments.pdf', $queryParams),
        ];
    }

    private function normalizePaymentStatus($paymentStatus): ?string
    {
        if ($paymentStatus === null) {
            return null;
        }

        $paymentStatus = trim((string) $paymentStatus);

        if ($paymentStatus === '' || in_array(strtolower($paymentStatus), ['null', 'all'], true)) {
            return null;
        }

        return $paymentStatus;
    }

    private function orderByCategory(Builder $query): Builder
    {
        if ($query->getConnection()->getDriverName() === 'sqlite') {
            return $query->orderBy('category');
        }

        return $query->orderByRaw("CAST(SUBSTRING_INDEX(category, '-', -1) AS UNSIGNED) ASC");
    }

    private function filterOptions(int $schoolId, int $year): array
    {
        $payments = Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereNull('deleted_at');

        $categories = Inscription::withTrashed()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereIn('id', (clone $payments)->select('inscription_id'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(fn (string $category) => ['value' => $category, 'label' => $category])
            ->values();

        $groups = TrainingGroup::withTrashed()
            ->where('school_id', $schoolId)
            ->where('name', '!=', 'Provisional')
            ->whereIn('id', (clone $payments)->select('training_group_id'))
            ->orderBy('name')
            ->get()
            ->map(fn (TrainingGroup $group) => [
                'value' => $group->id,
                'label' => $group->full_group,
            ])
            ->values();

        return compact('categories', 'groups');
    }

    private function decoratePayments(Collection $payments): Collection
    {
        $payments->loadMissing('inscription.player');
        $school = getSchool(auth()->user());
        $school->loadMissing('settingsValues');
        $historyFieldsByPayment = $this->historyFieldsByPayment($payments);

        return $payments->map(function (Payment $payment) use ($school, $historyFieldsByPayment): Payment {
            $inscription = $payment->inscription;
            $player = $inscription?->player;

            if ((int) $payment->school_id === (int) $school->id) {
                $payment->setRelation('school', $school);
            }

            if ($inscription && (int) $inscription->school_id === (int) $school->id) {
                $inscription->setRelation('school', $school);
            }

            if ($player) {
                $payment->setRelation('player', $player);
            }

            $payment->setAttribute(
                'default_monthly_amount',
                $this->paymentAmountResolver->monthlyAmountForPayment($payment)
            );
            $payment->setAttribute('inscription_deleted', (bool) $inscription?->trashed());
            $payment->setAttribute(
                'inscription_status_label',
                $inscription?->trashed() ? 'Retirada' : 'Activa'
            );
            $payment->setAttribute('history_fields', $historyFieldsByPayment[$payment->id] ?? []);

            return $payment;
        });
    }

    private function historyFieldsByPayment(Collection $payments): array
    {
        $paymentIds = $payments
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($paymentIds->isEmpty()) {
            return [];
        }

        return PaymentChangeLog::query()
            ->select('payment_id', 'field', DB::raw('COUNT(*) as changes_count'))
            ->whereIn('payment_id', $paymentIds)
            ->groupBy('payment_id', 'field')
            ->get()
            ->groupBy('payment_id')
            ->map(fn (Collection $logs) => $logs
                ->mapWithKeys(fn (PaymentChangeLog $log) => [
                    $log->field => (int) $log->changes_count,
                ])
                ->all())
            ->all();
    }
}
