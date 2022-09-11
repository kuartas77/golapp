<?php


namespace App\Http\ViewComposers\TrainingGroup;


use App\Models\Day;
use App\Models\User;
use App\Models\School;
use App\Traits\Commons;
use Illuminate\View\View;
use App\Models\Tournament;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TrainingGroupComposer
{
    use Commons;

    public function compose(View $view)
    {
        if (Auth::check()) {

            $school_id = isAdmin() ? 0 : getSchool(auth()->user())->id;

            $users = Cache::remember("KEY_USERS_{$school_id}", now()->addDay(), function () {
                return (new UserRepository(new User()))->getAll()->pluck('name', 'id');
            });

            $days = Cache::remember("KEY_DAYS", now()->addDay(), fn () =>
                Day::orderBy('days')->whereRelation('schedules', 'school_id', $school_id)->pluck('days', 'id')
            );

            $tournaments = Cache::remember("KEY_TOURNAMENT_{$school_id}", now()->addDay(), fn () =>
                Tournament::orderBy('name')->schoolId()->pluck('name', 'id')
            );

            $view->with('users', $users);
            $view->with('days', $days);
            $view->with('tournaments', $tournaments);
        }
    }
}
