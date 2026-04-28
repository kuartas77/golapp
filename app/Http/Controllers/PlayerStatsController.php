<?php

namespace App\Http\Controllers;

use App\Service\Player\PlayerStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerStatsController extends Controller
{
    public function __construct(private PlayerStatsService $playerStatsService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $school = getSchool(auth()->user());

        return response()->json(
            $this->playerStatsService->getRankingPayload(
                $school->id,
                $school->name ?? null,
                $request->only(['year', 'position', 'player_id', 'category'])
            )
        );
    }

    public function topPlayers(): JsonResponse
    {
        $school = getSchool(auth()->user());

        return response()->json($this->playerStatsService->getTopPlayersPayload($school->id));
    }

    public function playerDetail(int $id): JsonResponse
    {
        $school = getSchool(auth()->user());
        $payload = $this->playerStatsService->getPlayerDetailPayload($id, $school->id);

        if (!$payload) {
            return response()->json([
                'message' => 'Jugador no encontrado o sin estadísticas',
            ], 404);
        }

        return response()->json($payload);
    }
}
