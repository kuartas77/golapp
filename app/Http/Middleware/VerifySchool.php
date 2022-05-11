<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerifySchool
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check()){

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
        
        alert()->error(config('app.name'),__('messages.schools_dissabled'));
        
        return redirect('login');
    }
}
