<?php

namespace App\Http\Controllers\Reports;

use App\Exports\AttendanceRowsExport;
use App\Http\Controllers\Controller;
use App\Service\Assist\AttendanceReportService;
use App\Traits\PDFTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class AttendanceReportExportController extends Controller
{
    use PDFTrait;

    public function download(
        Request $request,
        string $report,
        string $format,
        AttendanceReportService $service
    ) {
        [$title, $subtitle, $rows] = $this->resolveReport($request, $report, $service);

        if ($format === 'xlsx') {
            return Excel::download(
                new AttendanceRowsExport($rows),
                $this->fileName($report, 'xlsx')
            );
        }

        $data = [
            'school' => getSchool(auth()->user()),
            'title' => $title,
            'subtitle' => $subtitle,
            'rows' => $rows,
        ];
        $this->setConfigurationMpdf(['format' => 'A4-L']);

        $this->createPDF($data, 'assists/table.blade.php');

        return $this->stream($this->fileName($report, 'pdf'));

    }

    private function resolveReport(Request $request, string $report, AttendanceReportService $service): array
    {
        $request->merge(['school_id' => getSchool(auth()->user())->id]);
        return match ($report) {
            'monthly-player' => $this->monthlyPlayerRows($request, $service),
            'monthly-group' => $this->monthlyGroupRows($request, $service),
            'annual-consolidated' => $this->annualConsolidatedRows($request, $service),
            default => abort(404),
        };
    }

    private function monthlyPlayerRows(Request $request, AttendanceReportService $service): array
    {
        $filters = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
        ]);

        $rows = $service->monthlyByPlayerQuery($filters)->get()->map(function ($row) {
            return [
                'Código' => $row->unique_code,
                'Jugador' => $row->player_name,
                'Grupo' => $row->training_group_name,
                'Año' => $row->year,
                'Mes' => $row->month,
                'Asistencias' => $row->total_asistencias,
                'Faltas' => $row->total_faltas,
                'Excusas' => $row->total_excusas,
                'Retiros' => $row->total_retiros,
                'Incapacidades' => $row->total_incapacidades,
                'Sesiones' => $row->total_sesiones_registradas,
                '% Asistencia' => $row->porcentaje_asistencia,
            ];
        })->values()->all();

        return [
            'Reporte mensual por jugador',
            "Año {$filters['year']} - Mes {$filters['month']}",
            $rows,
        ];
    }

    private function monthlyGroupRows(Request $request, AttendanceReportService $service): array
    {
        $filters = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
        ]);

        $rows = $service->monthlyByGroupQuery($filters)->get()->map(function ($row) {
            return [
                'Grupo' => $row->training_group_name,
                'Año' => $row->year,
                'Mes' => $row->month,
                'Jugadores' => $row->total_jugadores,
                'Asistencias' => $row->total_asistencias,
                'Faltas' => $row->total_faltas,
                'Excusas' => $row->total_excusas,
                'Retiros' => $row->total_retiros,
                'Incapacidades' => $row->total_incapacidades,
                'Sesiones' => $row->total_sesiones_registradas,
                '% Asistencia' => $row->porcentaje_asistencia,
            ];
        })->values()->all();

        return [
            'Reporte mensual por grupo',
            "Año {$filters['year']} - Mes {$filters['month']}",
            $rows,
        ];
    }

    private function annualConsolidatedRows(Request $request, AttendanceReportService $service): array
    {
        $filters = $request->validate([
            'year' => ['required', 'integer'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
        ]);

        $rows = $service->annualConsolidatedQuery($filters)->get()->map(function ($row) {
            return [
                'Código' => $row->unique_code,
                'Jugador' => $row->player_name,
                'Grupo' => $row->training_group_name,
                'Año' => $row->year,
                'Asistencias' => $row->total_asistencias,
                'Faltas' => $row->total_faltas,
                'Excusas' => $row->total_excusas,
                'Retiros' => $row->total_retiros,
                'Incapacidades' => $row->total_incapacidades,
                'Sesiones' => $row->total_sesiones_registradas,
                '% Asistencia' => $row->porcentaje_asistencia,
            ];
        })->values()->all();

        return [
            'Reporte anual consolidado',
            "Año {$filters['year']}",
            $rows,
        ];
    }

    private function fileName(string $report, string $extension): string
    {
        return "attendance-{$report}-" . now()->format('Ymd_His') . ".{$extension}";
    }
}
