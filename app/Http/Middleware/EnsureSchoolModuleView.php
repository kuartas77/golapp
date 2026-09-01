<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\SchoolModuleAccess;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSchoolModuleView
{
    public function handle(Request $request, Closure $next, string ...$moduleKeys): Response
    {
        $user = $request->user();
        $school = $user ? getSchool($user) : null;

        $allowed = $user && $school && collect($moduleKeys)
            ->every(fn (string $moduleKey): bool => SchoolModuleAccess::canView($user, $school, $moduleKey));

        if (! $allowed) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'message' => 'No tienes permiso para consultar este módulo.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para consultar este módulo.');
        }

        return $next($request);
    }
}
