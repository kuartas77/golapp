<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileUpdate;
use App\Http\Resources\API\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()
            ->profile()
            ->firstOrCreate([]);

        return (new ProfileResource($profile->load('user'), true))
            ->response()
            ->setStatusCode(200);
    }

    public function update(ProfileUpdate $request): JsonResponse
    {
        $profile = $request->user()
            ->profile()
            ->firstOrCreate([]);

        $profile->fill($request->validated());
        $profile->save();

        return (new ProfileResource($profile->load('user'), true))
            ->additional([
                'message' => 'Perfil actualizado correctamente.',
            ])
            ->response()
            ->setStatusCode(200);
    }
}
