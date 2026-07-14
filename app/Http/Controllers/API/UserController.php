<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\API\AuthUserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function check(Request $request)
    {

    }

    public function user(Request $request): AuthUserResource
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return new AuthUserResource($user);
    }
}
