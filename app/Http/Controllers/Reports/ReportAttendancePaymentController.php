<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Service\Reports\AttendancePaymentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportAttendancePaymentController extends Controller
{
    public function index()
    {
        return view('theme');
    }

    public function metadata(Request $request): JsonResponse
    {
        $previousMonth = now()->subMonthNoOverflow();
        $schoolId = getSchool(auth()->user())->id;

        $years = DB::table('vw_attendance_monthly_report_detail')
            ->select('year')
            ->where('school_id', $schoolId)
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->prepend($previousMonth->year)
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($year) => [
                'value' => (int) $year,
                'label' => (string) $year,
            ]);

        $defaultYear = (int) ($request->input('year') ?: $previousMonth->year);
        $defaultMonth = (int) ($request->input('month') ?: $previousMonth->month);
        $months = collect(config('variables.KEY_MONTHS_INDEX'))
            ->map(fn ($label, $value) => [
                'value' => (int) $value,
                'label' => $label,
            ])
            ->values();

        return response()->json([
            'years' => $years,
            'months' => $months,
            'groups' => $this->groupOptions($schoolId, $defaultYear, $defaultMonth),
            'defaultYear' => $defaultYear,
            'defaultMonth' => $defaultMonth,
        ]);
    }

    public function monthlyByGroup(Request $request, AttendancePaymentReportService $service): JsonResponse
    {
        $filters = $this->filters($request);
        $query = $service->monthlyByGroupQuery($filters);

        return datatables()->of($query)
            ->filterColumn('training_group_name', function ($query, $keyword) {
                $query->where('tg.name', 'like', "%{$keyword}%");
            })
            ->editColumn('flagged_percentage', fn ($row) => number_format((float) $row->flagged_percentage, 2) . '%')
            ->toJson();
    }

    public function monthlyByPlayer(Request $request, AttendancePaymentReportService $service): JsonResponse
    {
        $filters = $this->filters($request);
        $query = $service->monthlyByPlayerQuery($filters);

        return datatables()->of($query)
            ->filterColumn('player_name', function ($query, $keyword) {
                $query->where(function ($playerQuery) use ($keyword) {
                    $playerQuery->where('pl.names', 'like', "%{$keyword}%")
                        ->orWhere('pl.last_names', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('training_group_name', function ($query, $keyword) {
                $query->where('tg.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('payment_status_label', function ($query, $keyword) {
                $query->where('r.payment_status_label', 'like', "%{$keyword}%");
            })
            ->filterColumn('flag_reason', function ($query, $keyword) {
                $query->where('r.flag_reason', 'like', "%{$keyword}%");
            })
            ->toJson();
    }

    private function filters(Request $request): array
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
            'school_id' => (int) $data['school_id'],
            'training_group_id' => $data['training_group_id'] ?? null,
        ];
    }

    private function groupOptions(int $schoolId, int $year, int $month)
    {
        return DB::table('vw_attendance_monthly_report_detail as v')
            ->join('training_groups as tg', 'tg.id', '=', 'v.training_group_id')
            ->where('v.school_id', $schoolId)
            ->where('v.year', $year)
            ->where('v.month', $month)
            ->where('tg.name', '!=', 'Provisional')
            ->selectRaw('DISTINCT tg.id as value, tg.name, tg.category, tg.days, tg.schedules')
            ->orderBy('tg.name')
            ->get()
            ->map(fn ($group) => [
                'value' => $group->value,
                'label' => trim(sprintf(
                    '%s - (%s) %s %s',
                    $group->name,
                    $group->category,
                    $group->days,
                    $group->schedules
                )),
            ])
            ->values();
    }
}
