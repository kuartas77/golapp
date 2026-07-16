<?php

declare(strict_types=1);

namespace App\Http\Controllers\PlayerCredits;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerCreditMovementRequest;
use App\Models\Player;
use App\Service\PlayerCredits\PlayerCreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlayerCreditController extends Controller
{
    public function __construct(private PlayerCreditService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->list($this->schoolId(), $request->only(['search']))]);
    }

    public function datatable(Request $request): JsonResponse
    {
        return response()->json($this->service->datatable($this->schoolId(), $request->all()));
    }

    public function show(Player $player): JsonResponse
    {
        return response()->json(['data' => $this->service->detail($player, $this->schoolId())]);
    }

    public function storeMovement(PlayerCreditMovementRequest $request, Player $player): JsonResponse
    {
        $movement = $this->service->createManualMovement($player, $request->validated(), $this->schoolId(), (int) auth()->id());

        return response()->json([
            'message' => 'Movimiento de saldo registrado correctamente.',
            'data' => $movement,
            'balance' => $this->service->balanceForPlayer($this->schoolId(), (int) $player->id),
        ], Response::HTTP_CREATED);
    }

    private function schoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }
}
