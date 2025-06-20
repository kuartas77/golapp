<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Http\Resources\API\LoginResource;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login(Request $request): LoginResource
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

        return $this->generateResponse($user);
    }

    public function refresh(Request $request): LoginResource
    {
        $user = $request->user();
        $user->tokens()->delete();
        return $this->generateResponse($user);
    }

    private function generateResponse(User $user): LoginResource
    {
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
