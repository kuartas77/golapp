<?php

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\API\Notifications\Guardians\Concerns\ResolvesGuardianPlayers;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\Notification\TopicNotification\TopicNotificationCollection;
use App\Http\Resources\API\Notification\TopicNotification\TopicNotificationResource;
use App\Repositories\TopicNotificationRepository;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianTopicNotificationsController extends Controller
{
    use ResolvesGuardianPlayers;

    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private TopicNotificationRepository $repository
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new TopicNotificationCollection($this->repository->getPlayersNotifications($this->eligiblePlayers($request))))->resolve($request),
        ]);
    }

    public function show(Request $request, int $notification): JsonResponse
    {
        return response()->json([
            'data' => (new TopicNotificationResource($this->repository->getPlayersNotification($this->eligiblePlayers($request), $notification)))->resolve($request),
        ]);
    }

    public function read(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_id' => ['required', 'integer'],
            'player_id' => ['required', 'integer'],
        ]);

        $player = $this->guardianAccessService->findEligiblePlayer($this->guardian($request), (int) $validated['player_id']);
        $this->repository->markReadForPlayer($player, (int) $validated['notification_id']);

        return response()->json([
            'data' => [
                'success' => true,
            ],
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $this->repository->markReadAllForPlayers($this->eligiblePlayers($request));

        return response()->json([
            'data' => [
                'success' => true,
            ],
        ]);
    }
}
