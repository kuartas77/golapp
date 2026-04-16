<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Portal\GuardianProfileUpdateRequest;
use App\Http\Resources\API\Portal\GuardianUserResource;
use Illuminate\Http\JsonResponse;

class GuardianProfileController extends Controller
{
    public function update(GuardianProfileUpdateRequest $request): JsonResponse
    {
        $guardian = auth('guardians')->user();

        $guardian->forceFill([
            ...$request->validated(),
            'email_verified_at' => $guardian->email_verified_at ?? now(),
        ])->save();

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'data' => (new GuardianUserResource($guardian->refresh()))->resolve(),
        ]);
    }
}
