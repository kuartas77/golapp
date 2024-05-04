<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class SecondClickPreventMiddleware
{
    const TIME_LOCK = 2;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $url = $request->fullUrl();
        $data = json_encode($request->all());
        $method = $request->method();

        $lock = Cache::lock("secondclickprevent.$user->id.".md5("$method:$url:$data"), self::TIME_LOCK);
        if($lock->get()){
            return $next($request);
        }else{
            abort(404,"prevent double click");
        }
    }
}
