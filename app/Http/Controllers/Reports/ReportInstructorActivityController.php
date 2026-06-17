<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reports;

use App\Exports\AttendanceRowsExport;
use App\Http\Controllers\Controller;
use App\Service\Reports\InstructorActivityReportService;
use App\Traits\PDFTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportInstructorActivityController extends Controller
{
    use PDFTrait;

    public function index()
    {
        return view('theme');
    }

    public function metadata(InstructorActivityReportService $service): JsonResponse
    {
        $schoolId = (int) getSchool(auth()->user())->id;
        $previousMonth = now()->subMonthNoOverflow();

        return response()->json([
            'years' => $service->years($schoolId)
                ->map(fn (int $year) => [
                    'value' => $year,
                    'label' => (string) $year,
                ])
                ->values(),
            'months' => collect(config('variables.KEY_MONTHS_INDEX'))
                ->map(fn ($label, $value) => [
                    'value' => (int) $value,
                    'label' => $label,
                ])
                ->values(),
            'instructors' => $service->instructorOptions($schoolId),
            'defaultYear' => (int) $previousMonth->year,
            'defaultMonth' => (int) $previousMonth->month,
        ]);
    }

    public function activity(Request $request, InstructorActivityReportService $service): JsonResponse
    {
        $filters = $this->filters($request);

        return datatables()->collection($service->rows($filters))
            ->toJson();
    }

    public function download(
        Request $request,
        string $format,
        InstructorActivityReportService $service
    ) {
        $filters = $this->filters($request);
        $rows = $service->exportRows($filters);

        if ($format === 'xlsx') {
            return Excel::download(
                new AttendanceRowsExport($rows),
                $this->fileName('xlsx')
            );
        }

        $subtitle = sprintf(
            'Año %s - Mes %s',
            $filters['year'],
            $service->monthLabel((int) $filters['month'])
        );

        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF([
            'school' => getSchool(auth()->user()),
            'title' => 'Informe de actividad de instructores',
            'subtitle' => $subtitle,
            'rows' => $rows,
        ], 'assists/table.blade.php');

        return $this->stream($this->fileName('pdf'));
    }

    private function filters(Request $request): array
    {
        $request->merge(['school_id' => getSchool(auth()->user())->id]);

        return $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'between:1,12'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'instructor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function fileName(string $extension): string
    {
        return 'instructor-activity-' . now()->format('Ymd_His') . ".{$extension}";
    }
}
