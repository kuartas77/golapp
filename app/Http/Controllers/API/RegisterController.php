<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Service\API\RegisterService;
use App\Http\Requests\API\RegisterRequest;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request, RegisterService $registerService)
    {
        return response()->json($registerService->createUserSchoolUsesCase($request));
    }
}
