<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginPlayerController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Este acceso fue retirado. Ingresa mediante el Portal de Acudientes.',
        ], 410);
    }
}
