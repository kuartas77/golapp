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

            $school_id = isAdmin() ? 0 : auth()->user()->school->id;

            $users = Cache::remember("KEY_USERS_{$school_id}", now()->addDay(), function () {
                $school = auth()->user()->school->load('users');
                return $school->users->pluck('name','id');
            });

            $days = Cache::remember("KEY_DAYS_{$school_id}", now()->addDay(), fn () =>
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
