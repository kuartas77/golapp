<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class EnsureBackofficeUser
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user instanceof User) {
            return new JsonResponse([
                'message' => 'Una sesión de backoffice es requerida.',
            ], 401);
        }

        $previousDefaultGuard = Auth::getDefaultDriver();
        $webGuard = Auth::guard('web');
        $previousWebUser = $webGuard->user();

        Auth::shouldUse('web');
        $webGuard->setUser($user);
        $request->setUserResolver(fn (): User => $user);

        try {
            return $next($request);
        } finally {
            if ($previousWebUser !== null) {
                $webGuard->setUser($previousWebUser);
            } else {
                $webGuard->forgetUser();
            }

            Auth::shouldUse($previousDefaultGuard);
        }
    }
}
