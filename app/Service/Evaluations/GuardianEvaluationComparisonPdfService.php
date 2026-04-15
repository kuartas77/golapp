<?php

namespace App\Service\Evaluations;

use App\Models\Inscription;
use App\Traits\PDFTrait;

class GuardianEvaluationComparisonPdfService
{
    use PDFTrait;

    public function __construct(
        private PlayerEvaluationComparisonService $comparisonService
    ) {}

    public function download(Inscription $inscription, int $periodAId, int $periodBId)
    {
        $inscription->loadMissing([
            'player',
            'trainingGroup'
        ]);

        $comparison = $this->comparisonService->compare(
            inscription: $inscription,
            periodAId: $periodAId,
            periodBId: $periodBId
        );

        $this->setConfigurationMpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $playerName = $comparison['player']['name'] ?? 'jugador';

        $fileName = sprintf(
            'comparativo-evaluaciones-%s-%s.pdf',
            $inscription->id,
            now()->format('YmdHis')
        );

        $this->createPDF([
            'school' => getSchool(auth()->user()),
            'comparison' => $comparison,
            'clubName' => config('app.name'),
            'playerName' => $playerName,
        ], 'evaluations/guardian-evaluation-comparison-report');


        return $this->stream($fileName);
    }
}
