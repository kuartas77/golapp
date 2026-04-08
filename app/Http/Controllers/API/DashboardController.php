<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Service\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use function PHPSTORM_META\type;

class DashboardController extends Controller
{
    public function index(Request $request) {}

    public function kpis(Request $request)
    {

        $schoolId = getSchool(auth()->user())->id;

        $response = Cache::remember('statistics.school.' . $schoolId, now()->addMinutes(3), function () use ($schoolId) {

            // mensualidades x grupo
            $paymentByGroup = ReportService::paymentByGroupReport(year: now()->year, schoolId: $schoolId);
            $assistReport = ReportService::assistsPercentagesReport(year: now()->year, month: now()->month, schoolId: $schoolId);
            // $monthlyReport = ReportService::monthlyReport(year:now()->year, schoolId: $schoolId);
            // $generalReport = ReportService::generalReport(year:now()->year, schoolId: $schoolId);

            $paymentGroup = [
                'categories' => $paymentByGroup->pluck('grupo')->toArray(),
                'data' => [
                    // ['name' => 'Inscripciones', 'data' => $paymentByGroup->pluck('total_inscriptions')->toArray()],
                    ['name' => 'Pagas', 'data' => $paymentByGroup->pluck('monthly_payments_paid')->toArray()],
                    ['name' => 'Con Deuda', 'data' => $paymentByGroup->pluck('monthly_payments_debt')->toArray()],
                    ['name' => 'Becados', 'data' => $paymentByGroup->pluck('monthly_payments_scholarship')->toArray()],
                    ['name' => 'Otros', 'data' => $paymentByGroup->pluck('monthly_payments_others')->toArray()],
                ]
            ];

            $amountGroup = [
                'categories' => $paymentByGroup->pluck('grupo')->toArray(),
                'data' => [
                    ['type' => 'column', 'name' => 'Recauto total (incluye inscripción)', 'data' => $paymentByGroup->pluck('total_raised')->toArray()],
                    ['type' => 'column', 'name' => 'Inscripciones', 'data' => $paymentByGroup->pluck('total_enrollment')->toArray()],
                    ['type' => 'line', 'name' => '% de cumplimiento', 'data' => $paymentByGroup->pluck('percentage_compliance')->toArray()],
                ]
            ];


            $assistReportData = [
                'categories' => ['Asistencias', 'Excusas', 'Ausencias', 'Retiros', 'Incapacidades'],
                'data' => [
                    $assistReport->sum('total_attendances'),
                    $assistReport->sum('total_excuses'),
                    $assistReport->sum('total_absences'),
                    $assistReport->sum('total_retreat'),
                    $assistReport->sum('total_disabilities'),
                ]
            ];

            return [
                'payment_group_report' => $paymentGroup,
                'amount_payment_group_report' => $amountGroup,
                'assist_report' => $assistReportData
            ];
        });

        return response()->json($response);
    }
}
