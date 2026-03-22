<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Assist;
use App\Models\TrainingGroup;
use App\Service\Assist\AttendanceReportService;
use App\Traits\ErrorTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class ReportAssistsController extends Controller
{
    use ErrorTrait;

    public function index(Request $request)
    {
        $previousMonth = now()->subMonthNoOverflow();
        return view('reports.assists.index', []);
    }

    public function report(Request $request)
    {
        try {
            $date = now()->format('U');
            $year = $request->input('year');
            $groupName = ' ';

            if($request->filled('training_group_id')){
                $groupName = TrainingGroup::find($request->training_group_id)->full_group;
                $groupName = " grupo {$groupName} ";
            }

            $filename = "Asistencias del año {$year}{$groupName}{$date}.xlsx";







        } catch (\Throwable $th) {
            $this->logError("ReportAssistsController@report", $th);
            Alert::error(env('APP_NAME'), __('messages.error_general'));
        }
    }

    public function monthlyByPlayer(Request $request, AttendanceReportService $service): JsonResponse
    {
        $filters = $this->monthlyFilters($request);
        $query = $service->monthlyByPlayerQuery($filters);

        return datatables()->of($query)
            ->filterColumn('player_name', function ($query, $keyword) {
                $query->whereRaw("CONCAT(p.names, ' ', p.last_names) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('training_group_name', function ($query, $keyword) {
                $query->where('tg.name', 'like', "%{$keyword}%");
            })
            ->editColumn('porcentaje_asistencia', fn ($row) => number_format((float) $row->porcentaje_asistencia, 2) . '%')
            ->toJson();
    }

    public function monthlyByGroup(Request $request, AttendanceReportService $service): JsonResponse
    {
        $filters = $this->monthlyFilters($request);
        $query = $service->monthlyByGroupQuery($filters);

        return datatables()->of($query)
            ->filterColumn('training_group_name', function ($query, $keyword) {
                $query->where('tg.name', 'like', "%{$keyword}%");
            })
            ->editColumn('porcentaje_asistencia', fn ($row) => number_format((float) $row->porcentaje_asistencia, 2) . '%')
            ->toJson();
    }

    public function annualConsolidated(Request $request, AttendanceReportService $service): JsonResponse
    {
        $filters = $this->annualFilters($request);
        $query = $service->annualConsolidatedQuery($filters);

        return datatables()->of($query)
            ->filterColumn('player_name', function ($query, $keyword) {
                $query->whereRaw("CONCAT(p.names, ' ', p.last_names) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('training_group_name', function ($query, $keyword) {
                $query->where('tg.name', 'like', "%{$keyword}%");
            })
            ->editColumn('porcentaje_asistencia', fn ($row) => number_format((float) $row->porcentaje_asistencia, 2) . '%')
            ->toJson();
    }

    private function monthlyFilters(Request $request): array
    {
        $previousMonth = now()->subMonthNoOverflow();

        $request->merge(['school_id' => getSchool(auth()->user())->id]);

        $data = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
        ]);

        return [
            'year' => (int) ($data['year'] ?? $previousMonth->year),
            'month' => (int) ($data['month'] ?? $previousMonth->month),
            'school_id' => $data['school_id'],
            'training_group_id' => $data['training_group_id'] ?? null,
        ];
    }

    private function annualFilters(Request $request): array
    {
        $previousMonth = now()->subMonthNoOverflow();

        $request->merge(['school_id' => getSchool(auth()->user())->id]);

        $data = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
        ]);

        return [
            'year' => (int) ($data['year'] ?? $previousMonth->year),
            'school_id' => $data['school_id'],
            'training_group_id' => $data['training_group_id'] ?? null,
        ];
    }
}
