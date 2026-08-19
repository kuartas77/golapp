<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReceivedPaymentReport;
use App\Service\Reports\ReceivedPaymentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

final class ReportReceivedPaymentController extends Controller
{
    public function metadata(Request $request, ReceivedPaymentReportService $service): JsonResponse
    {
        $school = getSchool($request->user());
        $years = $service->years((int) $school->id);
        $defaultYear = (int) ($request->integer('year') ?: $years->last() ?: now()->year);

        return response()->json([
            'years' => $years->map(fn (int $year) => [
                'value' => $year,
                'label' => (string) $year,
            ])->values(),
            'groups' => $service->groupOptions((int) $school->id, $defaultYear),
            'defaultYear' => $defaultYear,
        ]);
    }

    public function pdf(Request $request, ReceivedPaymentReportService $service)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'training_group_id' => ['nullable', 'integer'],
            'player_search' => ['nullable', 'string', 'max:120'],
            'show_item_amounts' => ['nullable', 'boolean'],
            'show_total_paid' => ['nullable', 'boolean'],
        ]);

        try {
            $validated['school_id'] = getSchool($request->user())->id;
            $validated['training_group_id'] = (int) ($validated['training_group_id'] ?? 0);
            $validated['player_search'] = trim((string) ($validated['player_search'] ?? ''));

            return $service->exportPdf($validated, true);
        } catch (\Throwable $throwable) {
            report($throwable);

            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.error_general')], 500);
            }

            Alert::error(config('app.name'), __('messages.error_general'));

            return back();
        }
    }

    public function requestReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'player_search' => ['nullable', 'string', 'max:120'],
            'show_item_amounts' => ['nullable', 'boolean'],
            'show_total_paid' => ['nullable', 'boolean'],
        ]);
        $school = getSchool($request->user());
        $filters = [
            'year' => (int) $validated['year'],
            'training_group_id' => 0,
            'player_search' => trim((string) ($validated['player_search'] ?? '')),
            'show_item_amounts' => (bool) ($validated['show_item_amounts'] ?? true),
            'show_total_paid' => (bool) ($validated['show_total_paid'] ?? true),
        ];

        GenerateReceivedPaymentReport::dispatch(
            (int) $school->id,
            (int) $request->user()->getAuthIdentifier(),
            $filters,
        );

        return response()->json([
            'message' => 'El informe será enviado al correo electrónico registrado.',
        ], 202);
    }
}
