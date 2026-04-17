<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check() || !schoolCan($permission)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'message' => 'No tienes permiso para acceder a este módulo.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
