<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\Controller;
use App\Service\Competition\CompetitionStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompetitionStatsController extends Controller
{
    public function __construct(private CompetitionStatsService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'between:1900,2200'],
            'tournament_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);
        $school = getSchool(auth()->user());

        return response()->json($this->service->getRankingPayload(
            (int) $school->id,
            $filters,
            isInstructor() ? auth()->id() : null,
        ));
    }

    public function show(Request $request, int $group): JsonResponse
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'between:1900,2200'],
            'tournament_id' => ['nullable', 'integer'],
        ]);
        $school = getSchool(auth()->user());
        $payload = $this->service->getGroupPayload(
            $group,
            (int) $school->id,
            $filters,
            isInstructor() ? auth()->id() : null,
        );

        abort_if($payload === null, 404);

        return response()->json($payload);
    }
}
