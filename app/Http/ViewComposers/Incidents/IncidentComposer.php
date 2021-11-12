<?php


namespace App\Http\ViewComposers\Incidents;


use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class IncidentComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $users = Cache::remember(env('KEY_USERS'), now()->addDay(), function () {
                return User::where('id', '!=', 1)->orderBy('name')->pluck('name', 'id');
            });
            $view->with('users', $users);
        }
    }
}
