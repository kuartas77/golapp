<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\LoginResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::query()->where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->with(['roles', 'school']);

        $abilities = [];

        if ($user->hasRole(['super-admin'])) {
            $abilities = ['*'];
        } elseif ($user->hasRole(['school']) || $user->hasRole(['instructor'])) {
            $abilities = [
                'assists-index',
                'assists-update',
                'group-index',
                'group-show',
            ];
        }

        if (empty($abilities)) {
            throw ValidationException::withMessages(['user' => ['unknown user.']]);
        }

        $user->abilities = $abilities;

        return new LoginResource($user);
    }
}
