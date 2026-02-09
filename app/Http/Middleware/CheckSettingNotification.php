<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use function Laravel\Prompts\alert;

class CheckSettingNotification
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

            if (!filter_var($school->settings->get('SYSTEM_NOTIFY'), FILTER_VALIDATE_BOOLEAN)) {
                alert(config('app.name'), __('messages.schools_dissabled'));
                return redirect()->back();
            }
        }

        return $next($request);
    }
}
