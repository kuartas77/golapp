<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\PlayerEvaluation;
use App\Traits\PDFTrait;

class GuardianEvaluationPdfService
{
    use PDFTrait;

    public function __construct(
        private PlayerEvaluationScoreCalculator $calculator
    ) {}

    public function download(PlayerEvaluation $evaluation)
    {
        $evaluation->loadMissing([
            'school',
            'inscription.player',
            'inscription.school',
            'inscription.trainingGroup',
            'period',
            'template',
            'scores.criterion',
            'evaluator',
        ]);

        $dimensionScores = $this->calculator->calculateDimensionScores($evaluation);

        $this->setConfigurationMpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 12
        ]);

        $playerName = $evaluation->inscription->player?->full_names
            ?? $evaluation->inscription->player?->name
            ?? 'jugador';

        $school = $evaluation->school ?? $evaluation->inscription->school;

        $this->createPDF([
            'school' => $school,
            'evaluation' => $evaluation,
            'dimensionScores' => $dimensionScores,
            'playerName' => $playerName,
            'clubName' => $school->name,
        ], 'evaluations/guardian-evaluation-report.blade.php');

        $fileName = sprintf(
            'reporte-evaluacion-%s-%s.pdf',
            $evaluation->id,
            now()->format('YmdHis')
        );

         return $this->stream($fileName);




    }
}
