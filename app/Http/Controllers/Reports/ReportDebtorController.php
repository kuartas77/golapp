<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Service\Reports\DebtorReportService;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ReportDebtorController extends Controller
{
    use ErrorTrait;

    public function index(Request $request, DebtorReportService $debtorReportService)
    {
        $school = getSchool(auth()->user());

        if ($request->ajax()) {
            $year = (int) $request->input('year', now()->year);
            $groups = $debtorReportService->groupOptions($school->id, $year);
            $groups->prepend(['id' => 0, 'text' => 'Todos los grupos']);

            return response()->json($groups);
        }

        $years = $debtorReportService->years($school->id)->mapWithKeys(fn ($year) => [$year => $year]);

        return view('reports.debtors.index', compact('years'));
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
            $this->logError('ReportDebtorController@pdf', $th);
            Alert::error(env('APP_NAME'), __('messages.error_general'));

            return back();
        }
    }
}
