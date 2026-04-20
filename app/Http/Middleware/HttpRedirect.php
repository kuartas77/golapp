<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && (bool) config('app.force_https')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
