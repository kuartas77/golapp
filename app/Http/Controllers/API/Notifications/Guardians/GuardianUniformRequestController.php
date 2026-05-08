<?php

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\API\Notifications\Guardians\Concerns\ResolvesGuardianPlayers;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\GuardianUniformFormRequest;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestCollection;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestResource;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestStatistcsResource;
use App\Repositories\UniformRequestRepository;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianUniformRequestController extends Controller
{
    use ResolvesGuardianPlayers;

    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private UniformRequestRepository $repository
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new UniformRequestCollection($this->repository->uniformRequestsForPlayers($this->eligiblePlayers($request))))->resolve($request),
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new UniformRequestStatistcsResource($this->repository->uniformRequestsForPlayers($this->eligiblePlayers($request))))->resolve($request),
        ]);
    }

    public function show(Request $request, int $uniformRequest): JsonResponse
    {
        return response()->json([
            'data' => (new UniformRequestResource($this->repository->findPlayersRequestOrFail($this->eligiblePlayers($request), $uniformRequest)))->resolve($request),
        ]);
    }

    public function store(GuardianUniformFormRequest $request): JsonResponse
    {
        $player = $this->guardianAccessService->findEligiblePlayer($this->guardian($request), (int) $request->validated('player_id'));

        return response()->json([
            'data' => (new UniformRequestResource($this->repository->storeForPlayer($player, $request->validated())))->resolve($request),
        ], 201);
    }

    public function cancel(Request $request, int $uniformRequest): JsonResponse
    {
        $success = $this->repository->cancel(
            $this->repository->findPlayersRequestOrFail($this->eligiblePlayers($request), $uniformRequest)
        );

        return response()->json([
            'data' => [
                'success' => $success,
            ],
        ]);
    }
}
