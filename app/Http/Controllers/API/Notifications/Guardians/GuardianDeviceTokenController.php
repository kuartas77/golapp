<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notifications\GuardianDeviceTokenRequest;
use App\Models\GuardianDeviceToken;
use App\Models\People;
use Illuminate\Http\JsonResponse;

class GuardianDeviceTokenController extends Controller
{
    public function store(GuardianDeviceTokenRequest $request): JsonResponse
    {
        /** @var People|null $guardian */
        $guardian = $request->user();

        abort_unless($guardian instanceof People && $guardian->tutor === People::TUTOR, 403);

        $deviceToken = GuardianDeviceToken::query()->updateOrCreate(
            ['token' => $request->validated('token')],
            [
                'people_id' => $guardian->id,
                'platform' => $request->validated('platform'),
            ]
        );

        return response()->json([
            'data' => [
                'message' => 'Token del dispositivo registrado correctamente.',
                'platform' => $deviceToken->platform,
            ],
        ]);
    }
}
