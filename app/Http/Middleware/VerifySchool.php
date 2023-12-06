<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySchool
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {

            $school = getSchool(auth()->user());

            if (!isAdmin() && (empty($school) || !$school->is_enable)) {
                return $this->logout();
            }
        }

        return $next($request);
    }

    private function logout()
    {
        auth()->logout();

        alert()->error(config('app.name'), __('messages.schools_dissabled'));

        return redirect('login');
    }
}
