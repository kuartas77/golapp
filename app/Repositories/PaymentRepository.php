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
            COALESCE(SUM(case when january IN (0,2) then 1 else 0 end),0) january_due,
            COALESCE(SUM(case when january = 8 then 1 else 0 end),0) january_scholarship,
            COALESCE(SUM(case when february IN (1,9,10,11,12) then 1 else 0 end),0) february_payment,
            COALESCE(SUM(case when february IN (0,2) then 1 else 0 end),0) february_due,
            COALESCE(SUM(case when february = 8 then 1 else 0 end),0) february_scholarship,
            COALESCE(SUM(case when march IN (1,9,10,11,12) then 1 else 0 end),0) march_payment,
            COALESCE(SUM(case when march IN (0,2) then 1 else 0 end),0) march_due,
            COALESCE(SUM(case when march = 8 then 1 else 0 end),0) march_scholarship,
            COALESCE(SUM(case when april IN (1,9,10,11,12) then 1 else 0 end),0) april_payment,
            COALESCE(SUM(case when april IN (0,2) then 1 else 0 end),0) april_due,
            COALESCE(SUM(case when april = 8 then 1 else 0 end),0) april_scholarship,
            COALESCE(SUM(case when may IN (1,9,10,11,12) then 1 else 0 end),0) may_payment,
            COALESCE(SUM(case when may IN (0,2) then 1 else 0 end),0) may_due,
            COALESCE(SUM(case when may = 8 then 1 else 0 end),0) may_scholarship,
            COALESCE(SUM(case when june IN (1,9,10,11,12) then 1 else 0 end),0) june_payment,
            COALESCE(SUM(case when june IN (0,2) then 1 else 0 end),0) june_due,
            COALESCE(SUM(case when june = 8 then 1 else 0 end),0) june_scholarship,
            COALESCE(SUM(case when july IN (1,9,10,11,12) then 1 else 0 end),0) july_payment,
            COALESCE(SUM(case when july IN (0,2) then 1 else 0 end),0) july_due,
            COALESCE(SUM(case when july = 8 then 1 else 0 end),0) july_scholarship,
            COALESCE(SUM(case when august IN (1,9,10,11,12) then 1 else 0 end),0) august_payment,
            COALESCE(SUM(case when august IN (0,2) then 1 else 0 end),0) august_due,
            COALESCE(SUM(case when august = 8 then 1 else 0 end),0) august_scholarship,
            COALESCE(SUM(case when september IN (1,9,10,11,12) then 1 else 0 end),0) september_payment,
            COALESCE(SUM(case when september IN (0,2) then 1 else 0 end),0) september_due,
            COALESCE(SUM(case when september = 8 then 1 else 0 end),0) september_scholarship,
            COALESCE(SUM(case when october IN (1,9,10,11,12) then 1 else 0 end),0) october_payment,
            COALESCE(SUM(case when october IN (0,2) then 1 else 0 end),0) october_due,
            COALESCE(SUM(case when october = 8 then 1 else 0 end),0) october_scholarship,
            COALESCE(SUM(case when november IN (1,9,10,11,12) then 1 else 0 end),0) november_payment,
            COALESCE(SUM(case when november IN (0,2) then 1 else 0 end),0) november_due,
            COALESCE(SUM(case when november = 8 then 1 else 0 end),0) november_scholarship,
            COALESCE(SUM(case when december IN (1,9,10,11,12) then 1 else 0 end),0) december_payment,
            COALESCE(SUM(case when december IN (0,2) then 1 else 0 end),0) december_due,
            COALESCE(SUM(case when december = 8 then 1 else 0 end),0) december_scholarship")
            ->where('year', $year)
            ->when(!is_null($school_id), fn($query) => $query->where('school_id', $school_id))
            ->first();

        $labels = config('variables.KEY_LABEL_MONTHS');

        $series = collect();
        $payments = collect();
        $due = collect();
        $scholarship = collect();

        $payments->push((integer)$consult->january_payment);
        $payments->push((integer)$consult->february_payment);
        $payments->push((integer)$consult->march_payment);
        $payments->push((integer)$consult->april_payment);
        $payments->push((integer)$consult->may_payment);
        $payments->push((integer)$consult->june_payment);
        $payments->push((integer)$consult->july_payment);
        $payments->push((integer)$consult->august_payment);
        $payments->push((integer)$consult->september_payment);
        $payments->push((integer)$consult->october_payment);
        $payments->push((integer)$consult->november_payment);
        $payments->push((integer)$consult->december_payment);

        $due->push((integer)$consult->january_due);
        $due->push((integer)$consult->february_due);
        $due->push((integer)$consult->march_due);
        $due->push((integer)$consult->april_due);
        $due->push((integer)$consult->may_due);
        $due->push((integer)$consult->june_due);
        $due->push((integer)$consult->july_due);
        $due->push((integer)$consult->august_due);
        $due->push((integer)$consult->september_due);
        $due->push((integer)$consult->october_due);
        $due->push((integer)$consult->november_due);
        $due->push((integer)$consult->december_due);

        $scholarship->push((integer)$consult->january_scholarship);
        $scholarship->push((integer)$consult->february_scholarship);
        $scholarship->push((integer)$consult->march_scholarship);
        $scholarship->push((integer)$consult->april_scholarship);
        $scholarship->push((integer)$consult->may_scholarship);
        $scholarship->push((integer)$consult->june_scholarship);
        $scholarship->push((integer)$consult->july_scholarship);
        $scholarship->push((integer)$consult->august_scholarship);
        $scholarship->push((integer)$consult->september_scholarship);
        $scholarship->push((integer)$consult->october_scholarship);
        $scholarship->push((integer)$consult->november_scholarship);
        $scholarship->push((integer)$consult->december_scholarship);

        $series->push($payments);
        $series->push($due);
        $series->push($scholarship);
        return collect(['labels' => $labels, 'series' => $series]);
    }

}
