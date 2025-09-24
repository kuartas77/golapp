<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\UserResource;

class UserController extends Controller
{
    public function check(Request $request)
    {

    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['roles', 'school']));
    }
}
