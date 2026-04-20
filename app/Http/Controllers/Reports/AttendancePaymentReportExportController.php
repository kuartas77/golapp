<?php

namespace App\Http\Controllers\Reports;

use App\Exports\AttendanceRowsExport;
use App\Http\Controllers\Controller;
use App\Service\Reports\AttendancePaymentReportService;
use App\Traits\PDFTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendancePaymentReportExportController extends Controller
{
    use PDFTrait;

    public function download(
        Request $request,
        string $report,
        string $format,
        AttendancePaymentReportService $service
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

    private function resolveReport(Request $request, string $report, AttendancePaymentReportService $service): array
    {
        $request->merge(['school_id' => getSchool(auth()->user())->id]);

        return match ($report) {
            'monthly-group' => $this->monthlyGroupRows($request, $service),
            'monthly-player' => $this->monthlyPlayerRows($request, $service),
            default => abort(404),
        };
    }

    private function monthlyGroupRows(Request $request, AttendancePaymentReportService $service): array
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
                'Mes' => config("variables.KEY_MONTHS_INDEX.{$row->month}", $row->month),
                'Jugadores con asistencia' => $row->players_with_attendance,
                'Jugadores observados' => $row->flagged_players,
                'Asistencias' => $row->total_attendances,
                '% Observados' => $row->flagged_percentage,
            ];
        })->values()->all();

        return [
            'Reporte mensualidades vs asistencias por grupo',
            $this->subtitle($filters),
            $rows,
        ];
    }

    private function monthlyPlayerRows(Request $request, AttendancePaymentReportService $service): array
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
                'Mes' => config("variables.KEY_MONTHS_INDEX.{$row->month}", $row->month),
                'Asistencias' => $row->total_attendances,
                'Sesiones registradas' => $row->total_sessions_registered,
                'Estado mensualidad' => $row->payment_status_label,
                'Motivo' => $row->flag_reason,
            ];
        })->values()->all();

        return [
            'Reporte mensualidades vs asistencias por deportista',
            $this->subtitle($filters),
            $rows,
        ];
    }

    private function subtitle(array $filters): string
    {
        $monthLabel = config("variables.KEY_MONTHS_INDEX.{$filters['month']}", $filters['month']);

        return "Año {$filters['year']} - Mes {$monthLabel}";
    }

    private function fileName(string $report, string $extension): string
    {
        return "attendance-payment-{$report}-" . now()->format('Ymd_His') . ".{$extension}";
    }
}
