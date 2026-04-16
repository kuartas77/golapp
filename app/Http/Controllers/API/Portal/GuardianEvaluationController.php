<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Portal;

use App\Http\Controllers\Controller;
use App\Service\Evaluations\GuardianEvaluationPdfService;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\Request;

class GuardianEvaluationController extends Controller
{
    public function __construct(private GuardianAccessService $guardianAccessService)
    {
    }

    public function pdf(Request $request, int $evaluation, GuardianEvaluationPdfService $guardianEvaluationPdfService)
    {
        $guardian = $request->user();
        $evaluationModel = $this->guardianAccessService->findEligibleEvaluation($guardian, $evaluation);

        return $guardianEvaluationPdfService->download($evaluationModel);
    }
}
