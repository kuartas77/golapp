<?php

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\API\Notifications\Guardians\Concerns\ResolvesGuardianPlayers;
use App\Http\Controllers\Controller;
use App\Service\Portal\GuardianAccessService;
use App\Service\Portal\GuardianPlayerExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianPlayerExperienceController extends Controller
{
    use ResolvesGuardianPlayers;

    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private GuardianPlayerExperienceService $experienceService
    ) {
    }

    public function sportsSummary(Request $request, int $player): JsonResponse
    {
        return response()->json([
            'data' => $this->experienceService->sportsSummaryPayload($this->guardian($request), $player, $request),
        ]);
    }

    public function activity(Request $request, int $player): JsonResponse
    {
        return response()->json([
            'data' => $this->experienceService->activityPayload($this->guardian($request), $player, $request),
        ]);
    }
}
