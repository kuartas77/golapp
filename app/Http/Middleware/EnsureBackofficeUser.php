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
        $user = Auth::guard('web')->user();

        if (! $user instanceof User) {
            return new JsonResponse([
                'message' => 'Una sesión de backoffice es requerida.',
            ], 401);
        }

        Auth::shouldUse('web');
        $request->setUserResolver(fn (): User => $user);

        return $next($request);
    }
}
