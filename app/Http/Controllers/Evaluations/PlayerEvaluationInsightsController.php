<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\ComparePlayerEvaluationsRequest;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use App\Service\Evaluations\GuardianEvaluationComparisonPdfService;
use App\Service\Evaluations\GuardianEvaluationPdfService;
use App\Service\Evaluations\PlayerEvaluationComparisonService;

class PlayerEvaluationInsightsController extends Controller
{
    public function compare(
        ComparePlayerEvaluationsRequest $request,
        Inscription $inscription,
        PlayerEvaluationComparisonService $service
    ) {
        $data = $service->compare(
            inscription: $inscription->loadMissing(['player', 'trainingGroup']),
            periodAId: (int) $request->input('period_a_id'),
            periodBId: (int) $request->input('period_b_id'),
        );

        return response()->json([
            'message' => 'Comparativo generado correctamente.',
            'data' => $data,
        ]);
    }

    public function guardianReportPdf(
        Inscription $inscription,
        PlayerEvaluation $evaluation,
        GuardianEvaluationPdfService $pdfService
    ) {
        abort_if(
            (int) $evaluation->inscription_id !== (int) $inscription->id,
            404,
            'La evaluación no pertenece a la inscripción indicada.'
        );

        return $pdfService->download($evaluation);
    }

    public function guardianComparisonReportPdf(
        ComparePlayerEvaluationsRequest $request,
        Inscription $inscription,
        GuardianEvaluationComparisonPdfService $pdfService
    ) {
        return $pdfService->download(
            inscription: $inscription,
            periodAId: (int) $request->input('period_a_id'),
            periodBId: (int) $request->input('period_b_id'),
        );
    }
}
