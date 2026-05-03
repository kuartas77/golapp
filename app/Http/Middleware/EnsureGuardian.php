<?php

namespace App\Http\Middleware;

use App\Models\People;
use App\Service\Portal\GuardianAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureGuardian
{
    public function __construct(private GuardianAccessService $guardianAccessService)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        /** @var People|null $guardian */
        $guardian = Auth::guard('guardians')->user();

        if (!$guardian instanceof People) {
            return $this->unauthorized($request, 'No hay una sesión de acudiente activa.');
        }

        if (!$this->guardianAccessService->hasEligiblePlayers($guardian)) {
            Auth::guard('guardians')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $this->unauthorized($request, 'Tu acceso al portal está temporalmente bloqueado porque no tienes jugadores vigentes este año.');
        }

        Auth::shouldUse('guardians');
        $request->setUserResolver(fn () => Auth::guard('guardians')->user());

        return $next($request);
    }

    private function unauthorized(Request $request, string $message): mixed
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 401);
        }

        return redirect()
            ->route('portal.login.form')
            ->withErrors(['email' => $message]);
    }
}
