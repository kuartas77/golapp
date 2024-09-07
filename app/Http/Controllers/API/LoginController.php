<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

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

        $user = User::query()->with(['roles'])->where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $roles = Role::get(['id', 'name']);
        $abilities = [];
        foreach ($roles as $role) {
            if ($user->hasRole(['super-admin'])) {
                $abilities = ['*'];
            } elseif ($user->hasRole(['school'])) {
                $abilities = ['*'];
            } elseif ($user->hasRole(['instructor'])) {
                $abilities = [
                    'assists-index',
                    'assists-update',
                    'group-index',
                    'group-show',
                ];
            }
        }

        if (empty($abilities)) {
            throw ValidationException::withMessages(['user' => ['unknown user.']]);
        }

        $user->tokens()->delete();

        return response()->json([
            'token' => $user->createToken($request->input('email'), $abilities, now()->addWeeks(2))->plainTextToken
        ], Response::HTTP_OK);
    }
}
