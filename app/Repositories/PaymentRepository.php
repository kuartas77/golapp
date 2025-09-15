<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Inscription;
use App\Models\Payment;
use App\Traits\ErrorTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Throwable;

class PaymentRepository
{
    use ErrorTrait;

    public function __construct(private Payment $payment)
    {
        //
    }

    /**
     * @param $request
     * @param false $deleted
     */
    public function filter($request, bool $deleted = false, $raw = false): array
    {
        $payments = $this->filterSelect($request->all(), $deleted, $raw)->get();
        $payments->setAppends(['check_payments']);

        if(!$raw) {
            return $this->generateTable($payments, $deleted);
        }else {
            return $this->generateData($payments, $deleted);
        }
    }

    private function generateResponse($rows, $count, $urlExportExcel, $urlExportPDF, array $extra = [])
    {
        $response = [
            'rows' => $rows,
            'count' => $count,
            'url_export_excel' => $urlExportExcel,
            'url_export_pdf' => $urlExportPDF,
        ];

        return array_merge($response, $extra);
    }

    private function generateData($payments, $deleted)
    {
        $school = getSchool(auth()->user());
        $inscription_amount = (float) $school->settings['INSCRIPTION_AMOUNT'] ?? 70000;
        $monthly_payment = (float) $school->settings['MONTHLY_PAYMENT'] ?? 50000;
        $annuity = (float) $school->settings['ANNUITY'] ?? 48333;

        [$urlExportExcel, $urlExportPDF] = $this->generateLinks($payments, $deleted);

        $extra = [
            'inscription_amount' => $inscription_amount,
            'monthly_payment' => $monthly_payment,
            'annuity' => $annuity
        ];

        return $this->generateResponse($payments, $payments->count(), $urlExportExcel, $urlExportPDF, $extra);
    }

    private function generateTable($payments, $deleted)
    {
        $rows = '';
        $payments->setAppends(['check_payments']);
        $school = getSchool(auth()->user());
        $inscription_amount = $school->settings['INSCRIPTION_AMOUNT'] ?? 70000;
        $monthly_payment = $school->settings['MONTHLY_PAYMENT'] ?? 50000;
        $annuity = $school->settings['ANNUITY'] ?? 48333;
        $nameFields = config('variables.KEY_INDEX_MONTHS');
        $nameFields[0] = 'enrollment';
        ksort($nameFields);

        foreach ($payments as $payment) {
            $rows .= View::make('templates.payments.row', [
                'payment' => $payment,
                'deleted' => $deleted,
                'front' => true,
                'nameFields' => $nameFields,
                'inscription_amount' => $inscription_amount,
                'monthly_payment' => $monthly_payment,
                'annuity' => $annuity
            ])->render();
        }

        [$urlExportExcel, $urlExportPDF] = $this->generateLinks($payments, $deleted);

        return $this->generateResponse($rows, $payments->count(), $urlExportExcel, $urlExportPDF);
    }

    private function generateLinks($payments, $deleted)
    {
        $query_params = request()->query();
        if ($deleted) {
            $query_params['deleted'] = true;
        }

        if ($payments && !request()->filled('training_group_id')) {
            $query_params['training_group_id'] = 0;
        }

        ksort($query_params);

        return [
            route('export.payments.excel', $query_params),
            route('export.payments.pdf', $query_params)
        ];
    }

    /**
     * @param $params
     * @param false $deleted
     */
    public function filterSelect(array $params, bool $deleted = false, bool $raw = false): Builder
    {
        $school_id = data_get($params, 'school_id');
        $category = data_get($params, 'category');
        $year = data_get($params, 'year', now()->year);
        $unique_code = data_get($params, 'unique_code');
        $training_group_id = data_get($params, 'training_group_id', 0);

        $query = $this->payment->where('school_id', $school_id)
        ->when($raw,
            fn($q) => $q->with(['player']),
            fn($q) => $q->with(['inscription' => fn($query) => $query->with(['player'])->withTrashed()])->withTrashed()
        );

        $query
            ->addSelect([
                'category' => Inscription::query()->select('category')->whereColumn('inscriptions.id', 'inscription_id')->where('year', $year)->take(1)
            ])
            ->where('year', $year)
            ->whereHas('player')
            ->when($unique_code, fn($q) => $q->where('unique_code', $unique_code))
            ->when($training_group_id != 0, fn($q) => $q->where('training_group_id', $training_group_id))
            ->when($category, fn($q) => $q->whereHas('inscription', fn($inscription) => $inscription->where('year', $year)->where('category', $category)->withTrashed()))
            ->orderBy('inscription_id', 'asc');

        return $query;
    }

    public function filterSelectRaw(array $params, bool $deleted = false)
    {
        $school_id = data_get($params, 'school_id');
        $category = data_get($params, 'category');
        $year = data_get($params, 'year', now()->year);
        $unique_code = data_get($params, 'unique_code');
        $training_group_id = data_get($params, 'training_group_id', 0);

        return $this->payment->addSelect([
            'category' => Inscription::query()->select('category')->whereColumn('inscriptions.id', 'inscription_id')->where('year', $year)->take(1)
        ])
        ->where('school_id', $school_id)
        ->where('year', $year)
        ->when($unique_code, fn($q) => $q->where('unique_code', $unique_code))
        ->when($training_group_id != 0, fn($q) => $q->where('training_group_id', $training_group_id))
        ->when($category, fn($q) => $q->whereHas('inscription', fn($inscription) => $inscription->where('year', $year)->where('category', $category)->withTrashed()))
        ->withTrashed()
        ->orderBy('inscription_id', 'asc');
    }

    public function setPay(array $values, $payment)
    {
        $isPay = false;
        try {
            DB::beginTransaction();
            $isPay = $payment->fill($values)->save();
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->logError('PaymentRepository@setPay', $throwable);
            $isPay = false;
        }

        return $isPay;
    }

    public function dataGraphicsYear(int $year = 0): Collection
    {
        $year = $year == 0 ? now()->year : $year;
        $school_id = getSchool(auth()->user())->id;
        return Cache::remember(sprintf('graphics.year.%d.%s', $year, $school_id), now()->addMinute(), fn() => $this->queryGraphics($year, $school_id));
    }

    private function queryGraphics($year, $school_id)
    {
        $consult = DB::table('payments')->selectRaw("
            COALESCE(SUM(case when january IN (1,9,10,11,12) then 1 else 0 end),0) january_payment,
            COALESCE(SUM(case when january = 2 then 1 else 0 end),0) january_due,
            COALESCE(SUM(case when january = 8 then 1 else 0 end),0) january_scholarship,
            COALESCE(SUM(case when january = 0 then 1 else 0 end),0) january_pending,
            COALESCE(SUM(case when february IN (1,9,10,11,12) then 1 else 0 end),0) february_payment,
            COALESCE(SUM(case when february = 2 then 1 else 0 end),0) february_due,
            COALESCE(SUM(case when february = 8 then 1 else 0 end),0) february_scholarship,
            COALESCE(SUM(case when february = 0 then 1 else 0 end),0) february_pending,
            COALESCE(SUM(case when march IN (1,9,10,11,12) then 1 else 0 end),0) march_payment,
            COALESCE(SUM(case when march = 2 then 1 else 0 end),0) march_due,
            COALESCE(SUM(case when march = 8 then 1 else 0 end),0) march_scholarship,
            COALESCE(SUM(case when march = 0 then 1 else 0 end),0) march_pending,
            COALESCE(SUM(case when april IN (1,9,10,11,12) then 1 else 0 end),0) april_payment,
            COALESCE(SUM(case when april = 2 then 1 else 0 end),0) april_due,
            COALESCE(SUM(case when april = 8 then 1 else 0 end),0) april_scholarship,
            COALESCE(SUM(case when april = 0 then 1 else 0 end),0) april_pending,
            COALESCE(SUM(case when may IN (1,9,10,11,12) then 1 else 0 end),0) may_payment,
            COALESCE(SUM(case when may = 2 then 1 else 0 end),0) may_due,
            COALESCE(SUM(case when may = 8 then 1 else 0 end),0) may_scholarship,
            COALESCE(SUM(case when may = 0 then 1 else 0 end),0) may_pending,
            COALESCE(SUM(case when june IN (1,9,10,11,12) then 1 else 0 end),0) june_payment,
            COALESCE(SUM(case when june = 2 then 1 else 0 end),0) june_due,
            COALESCE(SUM(case when june = 8 then 1 else 0 end),0) june_scholarship,
            COALESCE(SUM(case when june = 0 then 1 else 0 end),0) june_pending,
            COALESCE(SUM(case when july IN (1,9,10,11,12) then 1 else 0 end),0) july_payment,
            COALESCE(SUM(case when july = 2 then 1 else 0 end),0) july_due,
            COALESCE(SUM(case when july = 8 then 1 else 0 end),0) july_scholarship,
            COALESCE(SUM(case when july = 0 then 1 else 0 end),0) july_pending,
            COALESCE(SUM(case when august IN (1,9,10,11,12) then 1 else 0 end),0) august_payment,
            COALESCE(SUM(case when august = 2 then 1 else 0 end),0) august_due,
            COALESCE(SUM(case when august = 8 then 1 else 0 end),0) august_scholarship,
            COALESCE(SUM(case when august = 0 then 1 else 0 end),0) august_pending,
            COALESCE(SUM(case when september IN (1,9,10,11,12) then 1 else 0 end),0) september_payment,
            COALESCE(SUM(case when september = 2 then 1 else 0 end),0) september_due,
            COALESCE(SUM(case when september = 8 then 1 else 0 end),0) september_scholarship,
            COALESCE(SUM(case when september = 0 then 1 else 0 end),0) september_pending,
            COALESCE(SUM(case when october IN (1,9,10,11,12) then 1 else 0 end),0) october_payment,
            COALESCE(SUM(case when october = 2 then 1 else 0 end),0) october_due,
            COALESCE(SUM(case when october = 8 then 1 else 0 end),0) october_scholarship,
            COALESCE(SUM(case when october = 0 then 1 else 0 end),0) october_pending,
            COALESCE(SUM(case when november IN (1,9,10,11,12) then 1 else 0 end),0) november_payment,
            COALESCE(SUM(case when november = 2 then 1 else 0 end),0) november_due,
            COALESCE(SUM(case when november = 8 then 1 else 0 end),0) november_scholarship,
            COALESCE(SUM(case when november = 0 then 1 else 0 end),0) november_pending,
            COALESCE(SUM(case when december IN (1,9,10,11,12) then 1 else 0 end),0) december_payment,
            COALESCE(SUM(case when december = 2 then 1 else 0 end),0) december_due,
            COALESCE(SUM(case when december = 8 then 1 else 0 end),0) december_scholarship,
            COALESCE(SUM(case when december = 0 then 1 else 0 end),0) december_pending")
            ->where('year', $year)
            ->when(!is_null($school_id), fn($query) => $query->where('school_id', $school_id))
            ->first();

        return $this->makeLabelAndSeries($consult);
    }

    private function makeLabelAndSeries($consult)
    {
        $labels = config('variables.KEY_LABEL_MONTHS');
        $series = collect();
        $arPayments = [];
        $arDue = [];
        $arSholar = [];
        $arPending = [];

        array_push($arPayments, (integer)$consult->january_payment);
        array_push($arPayments, (integer)$consult->february_payment);
        array_push($arPayments, (integer)$consult->march_payment);
        array_push($arPayments, (integer)$consult->april_payment);
        array_push($arPayments, (integer)$consult->may_payment);
        array_push($arPayments, (integer)$consult->june_payment);
        array_push($arPayments, (integer)$consult->july_payment);
        array_push($arPayments, (integer)$consult->august_payment);
        array_push($arPayments, (integer)$consult->september_payment);
        array_push($arPayments, (integer)$consult->october_payment);
        array_push($arPayments, (integer)$consult->november_payment);
        array_push($arPayments, (integer)$consult->december_payment);
        $payments = collect(['name' => 'Pagaron', 'data' => $arPayments]);

        array_push($arDue, (integer)$consult->january_due);
        array_push($arDue, (integer)$consult->february_due);
        array_push($arDue, (integer)$consult->march_due);
        array_push($arDue, (integer)$consult->april_due);
        array_push($arDue, (integer)$consult->may_due);
        array_push($arDue, (integer)$consult->june_due);
        array_push($arDue, (integer)$consult->july_due);
        array_push($arDue, (integer)$consult->august_due);
        array_push($arDue, (integer)$consult->september_due);
        array_push($arDue, (integer)$consult->october_due);
        array_push($arDue, (integer)$consult->november_due);
        array_push($arDue, (integer)$consult->december_due);
        $due = collect(['name' => 'Deben', 'data' => $arDue]);

        array_push($arSholar, (integer)$consult->january_scholarship);
        array_push($arSholar, (integer)$consult->february_scholarship);
        array_push($arSholar, (integer)$consult->march_scholarship);
        array_push($arSholar, (integer)$consult->april_scholarship);
        array_push($arSholar, (integer)$consult->may_scholarship);
        array_push($arSholar, (integer)$consult->june_scholarship);
        array_push($arSholar, (integer)$consult->july_scholarship);
        array_push($arSholar, (integer)$consult->august_scholarship);
        array_push($arSholar, (integer)$consult->september_scholarship);
        array_push($arSholar, (integer)$consult->october_scholarship);
        array_push($arSholar, (integer)$consult->november_scholarship);
        array_push($arSholar, (integer)$consult->december_scholarship);
        $scholarship = collect(['name' => 'Becados', 'data' => $arSholar]);

        array_push($arPending, (integer)$consult->january_pending);
        array_push($arPending, (integer)$consult->february_pending);
        array_push($arPending, (integer)$consult->march_pending);
        array_push($arPending, (integer)$consult->april_pending);
        array_push($arPending, (integer)$consult->may_pending);
        array_push($arPending, (integer)$consult->june_pending);
        array_push($arPending, (integer)$consult->july_pending);
        array_push($arPending, (integer)$consult->august_pending);
        array_push($arPending, (integer)$consult->september_pending);
        array_push($arPending, (integer)$consult->october_pending);
        array_push($arPending, (integer)$consult->november_pending);
        array_push($arPending, (integer)$consult->december_pending);
        $pending = collect(['name' => 'Pendientes', 'data' => $arPending]);

        $series->push($payments);
        $series->push($due);
        $series->push($scholarship);
        $series->push($pending);

        return collect(['labels' => $labels, 'series' => $series]);
    }

    public function queryPaymentsDueByMonth($schoolId, ?int $year = null, ?int $month = null, bool $onlyDue = true)
    {
        // '1' Pay
        // '2' Due
        if (!is_null($month) && $month < 1 || $month > 12) {
            $month = now()->month;
        }

        $months = config('variables.KEY_MONTHS_EN');
        $selectYear = is_null($year) ? now()->year : $year;
        $selectMonth = is_null($month) ? $months[now()->month] : $months[$month];

        return Payment::query()
            ->select([
                'payments.id',
                'payments.unique_code',
                'payments.updated_at',
                'players.email',
                DB::raw("CONCAT(players.names, ' ', players.last_names) as names"),
                DB::raw('training_groups.name as group_name'),
                $selectMonth,
            ])
            ->when(
                $onlyDue,
                fn($query) => $query->addSelect(DB::raw("'Due' as payment_status")),
                fn($query) => $query->addSelect(DB::raw("(CASE WHEN $selectMonth = '1' THEN 'Pay' WHEN $selectMonth = '2' THEN 'Due' END) as payment_status"))
            )
            ->join('inscriptions', 'inscriptions.id', '=', 'payments.inscription_id')
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->join('training_groups', 'training_groups.id', '=', 'payments.training_group_id')
            ->where('payments.year', $selectYear)
            ->whereRaw(DB::raw("payments.created_at <= (DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"))
            ->where('payments.school_id', $schoolId)
            ->when(
                $onlyDue,
                fn($query) => $query->where($selectMonth, '2'),
                fn($query) => $query->where(fn($query) => $query->where($selectMonth, '2')->orWhere($selectMonth, '1'))
            );
    }

    public function paymentsByStatus(array $params)
    {
        $schoolId = getSchool(auth()->user())->id;
        switch ($params['status']) {
            case '1':
                $status = ['1','9','10','11','12'];
                break;

            default:
               $status = [$params['status']];
                break;
        }


        return Payment::query()
            ->select([
                'payments.id',
                'payments.unique_code',
                DB::raw("CONCAT(players.names, ' ', players.last_names) as names"),
                DB::raw('training_groups.name as group_name'),
                'january',
                'february',
                'march',
                'april',
                'may',
                'june',
                'july',
                'august',
                'september',
                'october',
                'november',
                'december',
                'january_amount',
                'february_amount',
                'march_amount',
                'april_amount',
                'may_amount',
                'june_amount',
                'july_amount',
                'august_amount',
                'september_amount',
                'october_amount',
                'november_amount',
                'december_amount',
            ])
            ->join('inscriptions', 'inscriptions.id', '=', 'payments.inscription_id')
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->join('training_groups', 'training_groups.id', '=', 'payments.training_group_id')
            ->where(
                fn($q) =>
                $q->orWhereIn('january', $status)
                    ->orWhereIn('february', $status)
                    ->orWhereIn('march', $status)
                    ->orWhereIn('april', $status)
                    ->orWhereIn('may', $status)
                    ->orWhereIn('june', $status)
                    ->orWhereIn('july', $status)
                    ->orWhereIn('august', $status)
                    ->orWhereIn('september', $status)
                    ->orWhereIn('october', $status)
                    ->orWhereIn('november', $status)
                    ->orWhereIn('december', $status)
            )
            ->where('payments.year', now()->year)
            ->where('payments.school_id', $schoolId)->get();
    }

}
