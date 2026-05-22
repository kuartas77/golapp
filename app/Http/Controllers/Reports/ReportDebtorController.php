<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Service\Reports\DebtorReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ReportDebtorController extends Controller
{
    public function index(Request $request)
    {
        return view('theme');
    }

    public function metadata(Request $request, DebtorReportService $debtorReportService): JsonResponse
    {
        $school = getSchool(auth()->user());
        $years = $debtorReportService->years($school->id);
        $defaultYear = (int) ($request->input('year') ?: $years->last() ?: now()->year);

        return response()->json([
            'years' => $years->map(fn ($year) => [
                'value' => (int) $year,
                'label' => (string) $year,
            ])->values(),
            'groups' => $debtorReportService->groupOptions($school->id, $defaultYear),
            'defaultYear' => $defaultYear,
        ]);
    }

    public function pdf(Request $request, DebtorReportService $debtorReportService)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'training_group_id' => ['nullable', 'integer'],
            'show_total_debt' => ['nullable', 'boolean'],
        ]);

        try {
            $validated['school_id'] = getSchool(auth()->user())->id;
            $validated['training_group_id'] = (int) ($validated['training_group_id'] ?? 0);

            return $debtorReportService->exportPdf($validated, true);
        } catch (\Throwable $th) {
            report($th);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.error_general'),
                ], 500);
            }

            Alert::error(env('APP_NAME'), __('messages.error_general'));

            return back();
        }
    }
}
