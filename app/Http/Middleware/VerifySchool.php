<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

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

        Alert::error(config('app.name'), __('messages.schools_dissabled'));

        return redirect('login');
    }
}
