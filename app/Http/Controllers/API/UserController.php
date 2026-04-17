<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\AuthUserResource;

class UserController extends Controller
{
    public function check(Request $request)
    {

    }

    public function user(Request $request): AuthUserResource
    {
        return new AuthUserResource($request->user()->loadMissing('roles'));
    }
}
