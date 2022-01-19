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
                $school = auth()->user()->school->load('users');
                return $school->users->pluck('name','id');
            });

            $days = Cache::remember('KEY_DAYS', now()->addDay(), fn () =>
                Day::orderBy('days')->whereRelation('schedules', 'school_id', auth()->user()->school->id)->pluck('days', 'id')
            );

            $tournaments = Cache::remember('KEY_TOURNAMENT', now()->addDay(), fn () =>
                Tournament::orderBy('name')->schoolId()->pluck('name', 'id')
            );

            $view->with('users', $users);
            $view->with('days', $days);
            $view->with('tournaments', $tournaments);
        }
    }
}
