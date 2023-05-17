<?php


namespace App\Repositories;


use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class PaymentRepository
{

    public function __construct(private Payment $model)
    {
        //
    }

    /**
     * @param $request
     * @param false $deleted
     * @return array
     */
    public function filter($request, bool $deleted = false): array
    {
        $payments = $this->filterSelect($request, $deleted)->get();
        $rows = "";
        $payments->setAppends(['check_payments']);
        foreach ($payments as $pay) {
            $rows .= View::make('templates.payments.row', [
                'payment' => $pay,
                'deleted' => $deleted,
                'front' => true
            ])->render();
        }

        $query_params = request()->query();
        if ($deleted) {
            $query_params['deleted'] = true;
        }
        if($payments && !$request->filled('training_group_id')){
            $query_params['training_group_id'] = 0;
        }
        ksort($query_params);
        $urlExportExcel = route('export.payments.excel', $query_params);
        $urlExportPDF = route('export.payments.pdf', $query_params);
        return ['rows' => $rows, 'count' => $payments->count(), 'url_export_excel' => $urlExportExcel, 'url_export_pdf' => $urlExportPDF];
    }

    /**
     * @param $request
     * @param false $deleted
     * @return Builder
     */
    public function filterSelect($request, bool $deleted = false): Builder
    {
        $query = $this->model->query()->schoolId()->with('inscription.player');

        if ($deleted) {
            $query = $this->model->schoolId()->with([
                'inscription' => fn ($query) => $query->with('player')->withTrashed()
            ])->withTrashed();
        }

        $query->where('year', $request->input('year', now()->year))
        ->when($request->filled('unique_code'), fn ($q) => $q->where('unique_code', $request->unique_code))
        ->when($request->training_group_id != 0, fn ($q) => $q->where('training_group_id', $request->training_group_id))
        ->orderBy('inscription_id','asc');

        return $query;
    }

    public function setPay($request, $payment)
    {
        return $payment->fill(
            $request->only([
                'january', 'february', 'march',
                'april', 'may', 'june',
                'july', 'august', 'september',
                'october', 'november', 'december',
                'enrollment'
            ])
        )->save();
    }

    public function dataGraphicsYear(int $year = 0): Collection
    {
        $year = $year == 0 ? now()->year : $year;
        $school_id = (isSchool() || isInstructor()) ? getSchool(auth()->user())->id : null;

        return Cache::remember("graphics.year.{$year}.{$school_id}", now()->addMinute(), fn() => $this->queryGraphics($year, $school_id));
    }

    private function queryGraphics($year, $school_id)
    {
        $consult = DB::table('payments')->selectRaw("
            COALESCE(SUM(case when january = 1 then 1 else 0 end),0) january_payment,
            COALESCE(SUM(case when january IN (0,2) then 1 else 0 end),0) january_due,
            COALESCE(SUM(case when january = 8 then 1 else 0 end),0) january_scholarship,
            COALESCE(SUM(case when february = 1 then 1 else 0 end),0) february_payment,
            COALESCE(SUM(case when february IN (0,2) then 1 else 0 end),0) february_due,
            COALESCE(SUM(case when february = 8 then 1 else 0 end),0) february_scholarship,
            COALESCE(SUM(case when march = 1 then 1 else 0 end),0) march_payment,
            COALESCE(SUM(case when march IN (0,2) then 1 else 0 end),0) march_due,
            COALESCE(SUM(case when march = 8 then 1 else 0 end),0) march_scholarship,
            COALESCE(SUM(case when april = 1 then 1 else 0 end),0) april_payment,
            COALESCE(SUM(case when april IN (0,2) then 1 else 0 end),0) april_due,
            COALESCE(SUM(case when april = 8 then 1 else 0 end),0) april_scholarship,
            COALESCE(SUM(case when may = 1 then 1 else 0 end),0) may_payment,
            COALESCE(SUM(case when may IN (0,2) then 1 else 0 end),0) may_due,
            COALESCE(SUM(case when may = 8 then 1 else 0 end),0) may_scholarship,
            COALESCE(SUM(case when june = 1 then 1 else 0 end),0) june_payment,
            COALESCE(SUM(case when june IN (0,2) then 1 else 0 end),0) june_due,
            COALESCE(SUM(case when june = 8 then 1 else 0 end),0) june_scholarship,
            COALESCE(SUM(case when july = 1 then 1 else 0 end),0) july_payment,
            COALESCE(SUM(case when july IN (0,2) then 1 else 0 end),0) july_due,
            COALESCE(SUM(case when july = 8 then 1 else 0 end),0) july_scholarship,
            COALESCE(SUM(case when august = 1 then 1 else 0 end),0) august_payment,
            COALESCE(SUM(case when august IN (0,2) then 1 else 0 end),0) august_due,
            COALESCE(SUM(case when august = 8 then 1 else 0 end),0) august_scholarship,
            COALESCE(SUM(case when september = 1 then 1 else 0 end),0) september_payment,
            COALESCE(SUM(case when september IN (0,2) then 1 else 0 end),0) september_due,
            COALESCE(SUM(case when september = 8 then 1 else 0 end),0) september_scholarship,
            COALESCE(SUM(case when october = 1 then 1 else 0 end),0) october_payment,
            COALESCE(SUM(case when october IN (0,2) then 1 else 0 end),0) october_due,
            COALESCE(SUM(case when october = 8 then 1 else 0 end),0) october_scholarship,
            COALESCE(SUM(case when november = 1 then 1 else 0 end),0) november_payment,
            COALESCE(SUM(case when november IN (0,2) then 1 else 0 end),0) november_due,
            COALESCE(SUM(case when november = 8 then 1 else 0 end),0) november_scholarship,
            COALESCE(SUM(case when december = 1 then 1 else 0 end),0) december_payment,
            COALESCE(SUM(case when december IN (0,2) then 1 else 0 end),0) december_due,
            COALESCE(SUM(case when december = 8 then 1 else 0 end),0) december_scholarship")
            ->where('year', $year)
            ->when(!is_null($school_id), fn($query) => $query->where('school_id', $school_id))
            ->first();

            $labels = config('variables.KEY_LABEL_MONTHS');

            $series = collect();
            $payments = collect();
            $due = collect();

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

            $series->push($payments);
            $series->push($due);
            return collect(['labels' => $labels, 'series' => $series]);
    }

}
