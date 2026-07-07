<?php

namespace App\Service\Reports;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\TrainingGroup;
use App\Traits\PDFTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DebtorReportService
{
    use PDFTrait;

    public function years(int $schoolId): Collection
    {
        $paymentYears = Payment::query()
            ->where('school_id', $schoolId)
            ->distinct()
            ->pluck('year');

        $invoiceYears = Invoice::query()
            ->where('school_id', $schoolId)
            ->distinct()
            ->pluck('year');

        $customChargeYears = InscriptionCustomCharge::query()
            ->where('school_id', $schoolId)
            ->where('status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('invoice_item_id')
            ->whereNotNull('due_date')
            ->pluck('due_date')
            ->map(fn ($date) => Carbon::parse($date)->year);

        return $paymentYears
            ->merge($invoiceYears)
            ->merge($customChargeYears)
            ->push(now()->year)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
    }

    public function groupOptions(int $schoolId, int $year): Collection
    {
        $paymentGroupIds = Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->pluck('training_group_id');

        $invoiceGroupIds = Invoice::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->pluck('training_group_id');

        $customChargeGroupIds = InscriptionCustomCharge::query()
            ->where('inscription_custom_charges.school_id', $schoolId)
            ->where('inscription_custom_charges.status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('inscription_custom_charges.invoice_item_id')
            ->whereYear('inscription_custom_charges.due_date', $year)
            ->join('inscriptions', 'inscription_custom_charges.inscription_id', '=', 'inscriptions.id')
            ->pluck('inscriptions.training_group_id');

        return TrainingGroup::query()
            ->whereIn('id', $paymentGroupIds->merge($invoiceGroupIds)->merge($customChargeGroupIds)->filter()->unique()->values())
            ->schoolId()
            ->orderBy('name')
            ->get()
            ->map(fn ($group) => [
                'id' => $group->id,
                'text' => $group->full_schedule_group,
                'value' => $group->id,
                'label' => $group->full_schedule_group,
            ])
            ->values();
    }

    public function rows(array $filters): Collection
    {
        $schoolId = (int) data_get($filters, 'school_id');
        $year = (int) data_get($filters, 'year', now()->year);
        $trainingGroupId = (int) data_get($filters, 'training_group_id', 0);
        $inscriptionIds = collect(data_get($filters, 'inscription_ids', []))->map(fn ($id) => (int) $id)->all();
        $asOf = data_get($filters, 'as_of');
        $includeRowContext = (bool) data_get($filters, 'include_row_context', true);

        $rows = collect();
        $invoicedMonthlyKeys = $this->invoicedMonthlyKeys($schoolId, $year, $trainingGroupId, $inscriptionIds, $asOf);
        $appendedInvoiceMonthlyKeys = collect();

        $this->paymentsQuery($schoolId, $year, $trainingGroupId, $inscriptionIds, $includeRowContext)
            ->get()
            ->each(function (Payment $payment) use ($rows, $invoicedMonthlyKeys, $includeRowContext) {
                $monthlyDebts = $this->monthlyDebtsForPayment($payment, $invoicedMonthlyKeys);

                if ($monthlyDebts->isEmpty()) {
                    return;
                }

                $row = $this->baseRowFromPayment($payment, $includeRowContext);
                $row = $rows->get($row['inscription_id'], $row);

                $monthlyDebts->each(function (array $monthlyDebt) use (&$row) {
                    $row = $this->appendDebt($row, $monthlyDebt['label'], $monthlyDebt['amount']);
                });

                $rows->put($row['inscription_id'], $row);
            });

        $this->invoiceItemsQuery($schoolId, $year, $trainingGroupId, $inscriptionIds, $asOf, $includeRowContext)
            ->get()
            ->each(function (InvoiceItem $item) use ($rows, $appendedInvoiceMonthlyKeys, $includeRowContext) {
                $itemDebt = (float) $item->total;

                if ($itemDebt <= 0) {
                    return;
                }

                if ($item->payment_id && $item->month) {
                    $monthlyKey = $this->monthlyKey((int) $item->payment_id, (string) $item->month);

                    if ($appendedInvoiceMonthlyKeys->has($monthlyKey)) {
                        return;
                    }

                    $appendedInvoiceMonthlyKeys->put($monthlyKey, true);
                }

                $row = $this->baseRowFromInvoiceItem($item, $includeRowContext);
                $row = $rows->get($row['inscription_id'], $row);
                $row = $this->appendDebt($row, $this->itemLabel($item), $itemDebt);

                $rows->put($row['inscription_id'], $row);
            });

        $this->customChargesQuery($schoolId, $year, $trainingGroupId, $inscriptionIds, $asOf, $includeRowContext)
            ->get()
            ->each(function (InscriptionCustomCharge $charge) use ($rows, $includeRowContext) {
                $chargeDebt = (float) $charge->value;

                if ($chargeDebt <= 0) {
                    return;
                }

                $row = $this->baseRowFromCustomCharge($charge, $includeRowContext);
                $row = $rows->get($row['inscription_id'], $row);
                $row = $this->appendDebt($row, $this->customChargeLabel($charge), $chargeDebt);

                $rows->put($row['inscription_id'], $row);
            });

        return $rows
            ->filter(fn ($row) => $row['total_debt'] > 0)
            ->sort(function (array $left, array $right): int {
                $leftCategory = trim($left['category']);
                $rightCategory = trim($right['category']);

                if ($leftCategory === '' || $rightCategory === '') {
                    $categoryComparison = ($leftCategory === '') <=> ($rightCategory === '');
                } else {
                    $categoryComparison = strnatcasecmp($leftCategory, $rightCategory);
                }

                return $categoryComparison !== 0
                    ? $categoryComparison
                    : strcasecmp($left['student_name'], $right['student_name']);
            })
            ->values();
    }

    public function playerDebts(int $schoolId, int $playerId, Carbon $asOf): Collection
    {
        $inscriptionIds = Inscription::withTrashed()
            ->where('school_id', $schoolId)
            ->where('player_id', $playerId)
            ->pluck('id')
            ->all();

        if ($inscriptionIds === []) {
            return collect();
        }

        return $this->years($schoolId)
            ->flatMap(function (int $year) use ($schoolId, $inscriptionIds, $asOf) {
                return $this->rows([
                    'school_id' => $schoolId,
                    'year' => $year,
                    'inscription_ids' => $inscriptionIds,
                    'as_of' => $asOf,
                    'include_row_context' => false,
                ])->flatMap(fn (array $row) => collect($row['debt_items'])->map(fn (array $item) => [
                    'year' => $year,
                    'label' => $item['label'],
                    'amount' => (float) $item['amount'],
                ]));
            })
            ->values();
    }

    public function exportPdf(array $filters, bool $stream = true)
    {
        $school = getSchool(auth()->user());
        $rows = $this->rows($filters + ['school_id' => $school->id]);
        $date = now()->format('d-m-Y h:i:s A');

        $data = [
            'school' => $school,
            'rows' => $rows,
            'date' => $date,
            'year' => (int) data_get($filters, 'year', now()->year),
            'group' => $this->selectedGroupLabel($filters),
            'showItemAmounts' => filter_var(data_get($filters, 'show_item_amounts', false), FILTER_VALIDATE_BOOLEAN),
            'showTotalDebt' => filter_var(data_get($filters, 'show_total_debt', false), FILTER_VALIDATE_BOOLEAN),
        ];

        $filename = "Deudores {$date}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'debtors.blade.php', mark: false);

        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    private function paymentsQuery(
        int $schoolId,
        int $year,
        int $trainingGroupId,
        array $inscriptionIds = [],
        bool $includeRowContext = true,
    ) {
        return Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId))
            ->when($inscriptionIds !== [], fn ($query) => $query->whereIn('inscription_id', $inscriptionIds))
            ->when($includeRowContext, fn ($query) => $query->with([
                'inscription' => fn ($query) => $query->with(['player', 'trainingGroup']),
                'training_group',
            ]));
    }

    private function invoiceItemsQuery(
        int $schoolId,
        int $year,
        int $trainingGroupId,
        array $inscriptionIds = [],
        ?Carbon $asOf = null,
        bool $includeRowContext = true,
    ) {
        return InvoiceItem::query()
            ->where('is_paid', false)
            ->whereHas('invoice', function ($query) use ($schoolId, $year, $trainingGroupId, $inscriptionIds, $asOf) {
                $query->where('school_id', $schoolId)
                    ->where('year', $year)
                    ->whereIn('status', ['pending', 'partial'])
                    ->whereRaw('total_amount > paid_amount')
                    ->when($inscriptionIds !== [], fn ($query) => $query->whereIn('inscription_id', $inscriptionIds))
                    ->when($asOf !== null, fn ($query) => $query->whereDate('due_date', '<=', $asOf->toDateString()))
                    ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId));
            })
            ->with($includeRowContext
                ? ['invoice.inscription.player', 'invoice.trainingGroup']
                : ['invoice']);
    }

    private function customChargesQuery(
        int $schoolId,
        int $year,
        int $trainingGroupId,
        array $inscriptionIds = [],
        ?Carbon $asOf = null,
        bool $includeRowContext = true,
    ) {
        return InscriptionCustomCharge::query()
            ->where('school_id', $schoolId)
            ->where('status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('invoice_item_id')
            ->whereYear('due_date', $year)
            ->when($inscriptionIds !== [], fn ($query) => $query->whereIn('inscription_id', $inscriptionIds))
            ->when($asOf !== null, fn ($query) => $query->whereDate('due_date', '<=', $asOf->toDateString()))
            ->when($trainingGroupId !== 0, fn ($query) => $query->whereHas(
                'inscription',
                fn ($query) => $query->where('training_group_id', $trainingGroupId)
            ))
            ->with($includeRowContext
                ? ['inscription.player', 'inscription.trainingGroup', 'invoiceCustomItem']
                : ['invoiceCustomItem']);
    }

    private function invoicedMonthlyKeys(int $schoolId, int $year, int $trainingGroupId, array $inscriptionIds = [], ?Carbon $asOf = null): Collection
    {
        return InvoiceItem::query()
            ->where('is_paid', false)
            ->whereNotNull('payment_id')
            ->whereNotNull('month')
            ->whereHas('invoice', function ($query) use ($schoolId, $year, $trainingGroupId, $inscriptionIds, $asOf) {
                $query->where('school_id', $schoolId)
                    ->where('year', $year)
                    ->whereIn('status', ['pending', 'partial'])
                    ->when($inscriptionIds !== [], fn ($query) => $query->whereIn('inscription_id', $inscriptionIds))
                    ->when($asOf !== null, fn ($query) => $query->whereDate('due_date', '<=', $asOf->toDateString()))
                    ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId));
            })
            ->get(['payment_id', 'month'])
            ->mapWithKeys(fn ($item) => [$this->monthlyKey((int) $item->payment_id, (string) $item->month) => true]);
    }

    private function monthlyDebtsForPayment(Payment $payment, Collection $invoicedMonthlyKeys): Collection
    {
        return collect(Payment::paymentFields())->map(function (string $field) use ($payment, $invoicedMonthlyKeys) {
            if ((int) $payment->{$field} !== Payment::$debt) {
                return null;
            }

            if ($invoicedMonthlyKeys->has($this->monthlyKey((int) $payment->id, $field))) {
                return null;
            }

            $amountField = Payment::amountFieldFor($field);

            return [
                'label' => $this->monthLabel($field),
                'amount' => (float) ($amountField ? $payment->{$amountField} : 0),
            ];
        })->filter(fn (?array $debt) => $debt !== null && $debt['amount'] > 0)->values();
    }

    private function monthlyKey(int $paymentId, string $month): string
    {
        return "{$paymentId}:{$month}";
    }

    private function baseRowFromPayment(Payment $payment, bool $includeRowContext = true): array
    {
        if (! $includeRowContext) {
            return $this->baseRow(
                (int) $payment->inscription_id,
                null,
                $payment->unique_code,
                $payment->unique_code,
                null,
                null
            );
        }

        $inscription = $payment->inscription;
        $player = $inscription?->player;
        $group = $inscription?->trainingGroup ?? $payment->training_group;

        return $this->baseRow(
            (int) $payment->inscription_id,
            $player?->id,
            $payment->unique_code,
            $player?->full_names ?? $inscription?->player?->full_names ?? $payment->unique_code,
            $inscription?->category ?? $payment->category,
            $group?->full_group ?? $group?->name
        );
    }

    private function baseRowFromInvoiceItem(InvoiceItem $item, bool $includeRowContext = true): array
    {
        $invoice = $item->invoice;

        if (! $includeRowContext) {
            return $this->baseRow(
                (int) $invoice->inscription_id,
                null,
                '',
                $invoice->student_name,
                null,
                null
            );
        }

        $inscription = $invoice->inscription;
        $player = $inscription?->player;
        $group = $invoice->trainingGroup;

        return $this->baseRow(
            (int) $invoice->inscription_id,
            $player?->id,
            $player?->unique_code ?? $inscription?->unique_code ?? '',
            $player?->full_names ?? $invoice->student_name,
            $inscription?->category,
            $group?->full_group ?? $group?->name
        );
    }

    private function baseRowFromCustomCharge(InscriptionCustomCharge $charge, bool $includeRowContext = true): array
    {
        if (! $includeRowContext) {
            return $this->baseRow(
                (int) $charge->inscription_id,
                $charge->player_id ? (int) $charge->player_id : null,
                '',
                $charge->name,
                null,
                null
            );
        }

        $inscription = $charge->inscription;
        $player = $inscription?->player;
        $group = $inscription?->trainingGroup;

        return $this->baseRow(
            (int) $charge->inscription_id,
            $player?->id,
            $player?->unique_code ?? $inscription?->unique_code ?? '',
            $player?->full_names ?? $charge->name,
            $inscription?->category,
            $group?->full_group ?? $group?->name
        );
    }

    private function baseRow(int $inscriptionId, ?int $playerId, string $uniqueCode, string $studentName, ?string $category, ?string $trainingGroup): array
    {
        return [
            'player_id' => $playerId,
            'inscription_id' => $inscriptionId,
            'unique_code' => $uniqueCode,
            'student_name' => $studentName,
            'category' => $category ?? '',
            'training_group' => $trainingGroup ?? '',
            'debt_items' => [],
            'debt_label' => '',
            'total_debt' => 0.0,
        ];
    }

    private function appendDebt(array $row, string $label, float $amount): array
    {
        $row['debt_items'][] = [
            'label' => $label,
            'amount' => $amount,
        ];
        $row['debt_label'] = collect($row['debt_items'])->pluck('label')->implode(', ');
        $row['total_debt'] += $amount;

        return $row;
    }

    private function monthLabel(string $field): string
    {
        if ($field === 'enrollment') {
            return 'Matrícula';
        }

        return config("variables.KEY_INDEX_MONTHS_LABEL.{$field}", ucfirst($field));
    }

    private function itemLabel(InvoiceItem $item): string
    {
        $type = match ($item->type) {
            'monthly' => 'Mensualidad',
            'enrollment' => 'Matrícula',
            default => 'Item',
        };

        $invoiceNumber = $item->invoice?->invoice_number;
        $description = trim((string) $item->description);

        // return trim("{$invoiceNumber} - {$type}: {$description}", ' -:');
        return trim("{$description}", ' -:');
    }

    private function customChargeLabel(InscriptionCustomCharge $charge): string
    {
        return trim((string) ($charge->name ?: $charge->invoiceCustomItem?->name));
    }

    private function selectedGroupLabel(array $filters): string
    {
        $trainingGroupId = (int) data_get($filters, 'training_group_id', 0);

        if ($trainingGroupId === 0) {
            return 'Todos los grupos';
        }

        $group = TrainingGroup::query()->find($trainingGroupId);

        return $group?->full_group ?? 'Grupo seleccionado';
    }
}
