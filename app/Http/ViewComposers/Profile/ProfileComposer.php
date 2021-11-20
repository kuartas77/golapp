<?php


namespace App\Http\ViewComposers\Profile;


use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProfileComposer
{

    public function compose(View $view)
    {
        if (Auth::check()) {
            $genders = Cache::remember('KEY_GENDERS', now()->addYear(), function () {
                return config('variables.KEY_GENDERS');
            });

            $positions = Cache::remember('KEY_POSITIONS_ASSIGNED', now()->addYear(), function () {
                return config('variables.KEY_POSITIONS_ASSIGNED');
            });

            $view->with('genders', $genders);
            $view->with('positions', $positions);
        }
    }
}
