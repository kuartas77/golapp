<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\GuardianForgotPasswordRequest;
use App\Http\Requests\Portal\GuardianLoginRequest;
use App\Http\Requests\Portal\GuardianResetPasswordRequest;
use App\Models\People;
use App\Service\Portal\GuardianAccessService;
use App\Service\Portal\GuardianInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:guardians')->except('logout');
    }

    public function showLoginForm(): View
    {
        return view('portal.auth.login');
    }

    public function login(
        GuardianLoginRequest $request,
        GuardianAccessService $guardianAccessService
    ): RedirectResponse
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

        if (!$guardianAccessService->hasEligiblePlayers($guardian)) {
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

        return redirect()->intended(route('portal.guardians.home'));
    }

    public function showForgotPasswordForm(): View
    {
        return view('portal.auth.forgot-password');
    }

    public function sendResetLink(
        GuardianForgotPasswordRequest $request,
        GuardianAccessService $guardianAccessService,
        GuardianInvitationService $guardianInvitationService
    ): RedirectResponse
    {
        $guardian = People::query()
            ->where('tutor', true)
            ->where('email', $request->validated('email'))
            ->first();

        if ($guardian
            && $guardianInvitationService->hasUniqueTutorEmail($guardian)
            && $guardianAccessService->hasEligiblePlayers($guardian)
        ) {
            $token = Password::broker('guardians')->createToken($guardian);
            $guardian->sendPasswordResetNotification($token);
        }

        return back()->with('status', 'Si existe una cuenta válida, recibirás instrucciones en tu correo.');
    }

    public function showResetForm(Request $request): View
    {
        return view('portal.auth.reset-password', [
            'token' => (string) $request->query('token'),
            'email' => (string) $request->query('email'),
        ]);
    }

    public function resetPassword(GuardianResetPasswordRequest $request): RedirectResponse
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

        return redirect()
            ->route('portal.login.form')
            ->with('status', 'La contraseña fue actualizada correctamente. Ya puedes ingresar.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('guardians')->logout();
        Auth::guard('players')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect(route('portal.login.form'));
    }
}
