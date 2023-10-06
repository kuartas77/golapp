<?php


namespace App\Http\ViewComposers\TrainingGroup;


use App\Models\Schedule;
use App\Models\User;
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

            $school_id = getSchool(auth()->user())->id;

            $days = Cache::rememberForever('KEY_WEEKS', fn () => config('variables.KEY_WEEKS'));

            $users = Cache::remember("KEY_USERS_{$school_id}", now()->addMinute(), fn () =>
                 (new UserRepository(new User()))->getAll()->pluck('name', 'id')
            );

            $schedules = Cache::remember("SCHEDULES_{$school_id}", now()->addMinute(), fn () =>
                Schedule::query()->schoolId()->pluck('schedule', 'schedule')
            );

            $tournaments = Cache::remember("KEY_TOURNAMENT_{$school_id}", now()->addDay(), fn () =>
                Tournament::orderBy('name')->schoolId()->pluck('name', 'id')
            );

            $view->with('users', $users);
            $view->with('days', $days);
            $view->with('schedules', $schedules);
            $view->with('tournaments', $tournaments);
        }
    }
}
