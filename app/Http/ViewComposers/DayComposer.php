<?php


namespace App\Http\ViewComposers;


use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DayComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $week = Cache::remember(env('KEY_WEEKS'), now()->addYear(), function () {
                return config('variables.KEY_WEEKS');
            });
            $view->with('week', $week);
        }
    }
}
