<?php

namespace App\Service\Payment;

use App\Models\Payment;
use App\Service\ReportService;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentAnalyticsService
{
    public function dataGraphicsYear(int $year = 0): Collection
    {
        $year = $year == 0 ? now()->year : $year;
        $schoolId = getSchool(auth()->user())->id;

        return Cache::remember(
            sprintf('graphics.year.%d.%s', $year, $schoolId),
            now()->addMinute(),
            fn () => $this->queryGraphics($year, $schoolId)
        );
    }

    public function queryPaymentsDueByMonth(int $schoolId, ?int $year = null, ?int $month = null, bool $onlyDue = true): QueryBuilder
    {
        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }

        $months = config('variables.KEY_INDEX_MONTHS');
        $selectedYear = $year ?? now()->year;
        $selectedMonthNumber = $month ?? now()->month;
        $selectedMonthColumn = $months[$selectedMonthNumber];
        $qualifiedMonthColumn = "payments.{$selectedMonthColumn}";

        return DB::table('payments')
            ->select([
                'payments.id',
                'payments.unique_code',
                'payments.updated_at',
                'players.email',
                DB::raw("CONCAT(players.names, ' ', players.last_names) as names"),
                DB::raw('training_groups.name as group_name'),
                DB::raw("{$qualifiedMonthColumn} as payment_month_value"),
            ])
            ->when(
                $onlyDue,
                fn ($query) => $query->selectRaw("'Due' as payment_status"),
                fn ($query) => $query->selectRaw("
                    CASE
                        WHEN {$qualifiedMonthColumn} = '1' THEN 'Pay'
                        WHEN {$qualifiedMonthColumn} = '2' THEN 'Due'
                        ELSE 'Unknown'
                    END as payment_status
                ")
            )
            ->join('inscriptions', 'inscriptions.id', '=', 'payments.inscription_id')
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->join('training_groups', 'training_groups.id', '=', 'payments.training_group_id')
            ->where('payments.year', $selectedYear)
            ->whereDate('payments.created_at', '<=', now()->subMonthNoOverflow()->toDateString())
            ->where('payments.school_id', $schoolId)
            ->when(
                $onlyDue,
                fn ($query) => $query->where($qualifiedMonthColumn, '2'),
                fn ($query) => $query->where(function ($query) use ($qualifiedMonthColumn): void {
                    $query->where($qualifiedMonthColumn, '2')
                        ->orWhere($qualifiedMonthColumn, '1');
                })
            );
    }

    public function paymentsByStatus(array $params)
    {
        $schoolId = getSchool(auth()->user())->id;
        switch ($params['status']) {
            case '1':
                $status = ['1', '9', '10', '11', '12'];
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
                fn ($q) => $q->orWhereIn('january', $status)
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
            ->where('payments.school_id', $schoolId)
            ->get();
    }

    public function dataGraphicsDashboard()
    {
        $schoolId = getSchool(auth()->user())->id;

        return Cache::remember('statistics.school.' . $schoolId, 1, function () use ($schoolId) {
            $paymentByGroup = ReportService::paymentByGroupReport(year: now()->year, schoolId: $schoolId, groupId: null);
            $assistReport = ReportService::assistsPercentagesReport(year: now()->year, month: now()->month, groupId: null, schoolId: $schoolId);
            $monthlyReport = ReportService::monthlyReport(year: now()->year, schoolId: $schoolId, groupId: null)->first();

            $paymentGroup = [
                'categories' => $paymentByGroup->pluck('grupo')->toArray(),
                'data' => [
                    ['name' => 'Pagos', 'data' => $paymentByGroup->pluck('monthly_payments_paid')->toArray()],
                    ['name' => 'Deben', 'data' => $paymentByGroup->pluck('monthly_payments_debt')->toArray()],
                    ['name' => 'Becados', 'data' => $paymentByGroup->pluck('monthly_payments_scholarship')->toArray()],
                ],
            ];

            $amountGroup = [
                'categories' => $paymentByGroup->pluck('grupo')->toArray(),
                'data' => [
                    ['type' => 'column', 'name' => 'Mensualidades', 'data' => $paymentByGroup->pluck('total_raised')->toArray()],
                    ['type' => 'column', 'name' => 'Inscripciones', 'data' => $paymentByGroup->pluck('total_enrollment')->toArray()],
                    ['type' => 'line', 'name' => '% de cumplimiento', 'data' => $paymentByGroup->pluck('percentage_compliance')->toArray()],
                ],
            ];

            $assistReportData = [
                'categories' => ['Asistencias', 'Excusas', 'Ausencias', 'Retiros', 'Incapacidades'],
                'data' => [
                    $assistReport->sum('total_attendances'),
                    $assistReport->sum('total_excuses'),
                    $assistReport->sum('total_absences'),
                    $assistReport->sum('total_retreat'),
                    $assistReport->sum('total_disabilities'),
                ],
            ];

            $months = array_keys(config('variables.KEY_INDEX_MONTHS_LABEL', []));
            $valueMonths = [];
            $paymentByMonth = [];

            if (! is_null($monthlyReport)) {
                foreach ($months as $month) {
                    $valueMonths[] = $monthlyReport->{$month};
                    $paymentByMonth[] = (int) $monthlyReport->{'payments_' . $month};
                }
            }

            $amountReportMonthly = [
                'categories' => array_values(config('variables.KEY_INDEX_MONTHS_LABEL', [])),
                'data' => [
                    ['type' => 'column', 'name' => 'Valor', 'data' => $valueMonths],
                    ['type' => 'column', 'name' => 'Pagos', 'data' => $paymentByMonth],
                ],
            ];

            return [
                'payment_group_report' => $paymentGroup,
                'amount_payment_group_report' => $amountGroup,
                'assist_report' => $assistReportData,
                'monthly_report' => $amountReportMonthly,
            ];
        });
    }

    private function queryGraphics($year, $schoolId): Collection
    {
        $monthlyStatuses = DB::table('vw_payments_report_detail')
            ->select([
                'month_number',
                'period_key',
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status_code IN (1, 9, 10, 11, 12, 15) THEN 1 ELSE 0 END), 0) AS payment')
            ->selectRaw('COALESCE(SUM(CASE WHEN status_code = 2 THEN 1 ELSE 0 END), 0) AS due')
            ->selectRaw('COALESCE(SUM(CASE WHEN status_code = 8 THEN 1 ELSE 0 END), 0) AS scholarship')
            ->selectRaw('COALESCE(SUM(CASE WHEN status_code = 0 THEN 1 ELSE 0 END), 0) AS pending')
            ->where('payment_year', $year)
            ->where('is_monthly', 1)
            ->when(! is_null($schoolId), fn ($query) => $query->where('school_id', $schoolId))
            ->groupBy('month_number', 'period_key')
            ->orderBy('month_number')
            ->get()
            ->keyBy('period_key');

        return $this->makeLabelAndSeries($monthlyStatuses);
    }

    private function makeLabelAndSeries(Collection $monthlyStatuses): Collection
    {
        $labels = config('variables.KEY_LABEL_MONTHS');

        return collect([
            'labels' => $labels,
            'series' => collect([
                collect(['name' => 'Pagaron', 'data' => $this->seriesData($monthlyStatuses, 'payment')]),
                collect(['name' => 'Deben', 'data' => $this->seriesData($monthlyStatuses, 'due')]),
                collect(['name' => 'Becados', 'data' => $this->seriesData($monthlyStatuses, 'scholarship')]),
                collect(['name' => 'Pendientes', 'data' => $this->seriesData($monthlyStatuses, 'pending')]),
            ]),
        ]);
    }

    private function seriesData(Collection $monthlyStatuses, string $field): array
    {
        return collect(config('variables.KEY_INDEX_MONTHS'))
            ->values()
            ->map(fn (string $month) => (int) ($monthlyStatuses->get($month)?->{$field} ?? 0))
            ->all();
    }
}
