<?php


namespace App\Http\ViewComposers\Incidents;


use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class IncidentComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $school_id = getSchool(auth()->user())->id;
            $users = Cache::remember("KEY_USERS_{$school_id}", now()->addMinute(), function () {
                return (new UserRepository(new User()))->getAll()->pluck('name', 'id');
            });
            $view->with('users', $users);
        }
    }
}
