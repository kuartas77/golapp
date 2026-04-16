<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Portal\GuardianForgotPasswordRequest;
use App\Http\Requests\API\Portal\GuardianLoginRequest;
use App\Http\Requests\API\Portal\GuardianResetPasswordRequest;
use App\Http\Resources\API\Portal\GuardianUserResource;
use App\Models\People;
use App\Service\Portal\GuardianAccessService;
use App\Service\Portal\GuardianInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class GuardianAuthController extends Controller
{
    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private GuardianInvitationService $guardianInvitationService
    ) {
    }

    public function login(GuardianLoginRequest $request): JsonResponse
    {
        $email = (string) $request->validated('email');

        $guardians = People::query()
            ->where('tutor', true)
            ->where('email', $email)
            ->get();

        if ($guardians->count() !== 1) {
            throw ValidationException::withMessages([
                'email' => ['No fue posible identificar una cuenta única para este correo.'],
            ]);
        }

        /** @var People $guardian */
        $guardian = $guardians->first();

        if (blank($guardian->password) || !Hash::check((string) $request->validated('password'), $guardian->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!$this->guardianAccessService->hasEligiblePlayers($guardian)) {
            throw ValidationException::withMessages([
                'email' => ['Tu acceso está temporalmente bloqueado porque no tienes jugadores vigentes este año o se ha deshabilitado la plataforma de acudientes.'],
            ]);
        }

        Auth::guard('guardians')->login($guardian);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $guardian->forceFill([
            'last_login_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Autenticado correctamente.',
        ]);
    }

    public function me(Request $request): GuardianUserResource
    {
        return new GuardianUserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('guardians')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logout exitoso.',
        ]);
    }

    public function forgotPassword(GuardianForgotPasswordRequest $request): JsonResponse
    {
        $guardian = People::query()
            ->where('tutor', true)
            ->where('email', $request->validated('email'))
            ->first();

        if ($guardian && $this->guardianInvitationService->hasUniqueTutorEmail($guardian) && $this->guardianAccessService->hasEligiblePlayers($guardian)) {
            $token = Password::broker('guardians')->createToken($guardian);
            $guardian->sendPasswordResetNotification($token);
        }

        return response()->json([
            'message' => 'Si existe una cuenta válida, recibirás instrucciones en tu correo.',
        ]);
    }

    public function resetPassword(GuardianResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('guardians')->reset(
            $request->validated(),
            function (People $guardian, string $password) {
                $guardian->forceFill([
                    'password' => $password,
                    'email_verified_at' => $guardian->email_verified_at ?? now(),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'La contraseña fue actualizada correctamente.',
        ]);
    }
}
