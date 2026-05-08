<?php

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\API\Notifications\Guardians\Concerns\ResolvesGuardianPlayers;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\Portal\GuardianPlayerListResource;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianPlayerController extends Controller
{
    use ResolvesGuardianPlayers;

    public function __construct(private GuardianAccessService $guardianAccessService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => GuardianPlayerListResource::collection($this->eligiblePlayers($request))->resolve($request),
        ]);
    }
}
