<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LoginPlayerController extends Controller
{

    public function login(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Este acceso fue retirado. Ingresa mediante el Portal de Acudientes.',
        ], 410);
    }
}
