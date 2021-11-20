<?php


namespace App\Http\ViewComposers\TrainingGroup;


use App\Models\Day;
use App\Models\Tournament;
use App\Traits\Commons;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TrainingGroupComposer
{
    use Commons;

    public function compose(View $view)
    {
        if (Auth::check()) {
            $users = Cache::remember('KEY_USERS', now()->addDay(), function () {
                return User::where('id', '!=', 1)->orderBy('name')->pluck('name', 'id');
            });

            $days = Cache::remember('KEY_DAYS', now()->addDay(), function () {
                return Day::orderBy('days')->pluck('days', 'id');
            });

            $tournaments = Cache::remember('KEY_TOURNAMENT', now()->addDay(), function () {
                return Tournament::orderBy('name')->pluck('name', 'id');
            });

            $view->with('users', $users);
            $view->with('days', $days);
            $view->with('tournaments', $tournaments);
        }
    }
}
