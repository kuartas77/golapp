<?php


namespace App\Http\ViewComposers\TrainingGroup;


use App\Models\User;
use App\Traits\Commons;
use App\Models\Schedule;
use Carbon\CarbonPeriod;
use Illuminate\View\View;
use App\Models\Tournament;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TrainingGroupComposer
{
    use Commons;

    public function compose(View $view): void
    {
        if (Auth::check()) {

            $school_id = getSchool(auth()->user())->id;

            $days = Cache::rememberForever('KEY_WEEKS', fn () => config('variables.KEY_WEEKS'));

            $users = Cache::remember("KEY_USERS_{$school_id}", now()->addMinute(), fn () => (new UserRepository(new User()))->getAll()->pluck('name', 'id'));

            $schedules = Cache::remember("SCHEDULES_{$school_id}", now()->addMinute(), fn () => Schedule::query()->schoolId()->pluck('schedule', 'schedule'));

            $tournaments = Cache::remember("KEY_TOURNAMENT_{$school_id}", now()->addDay(), fn () => Tournament::orderBy('name')->schoolId()->pluck('name', 'id'));

            $years = Cache::remember("KEY_YEARS_{$school_id}", now()->addDay(), function () {
                $now = Carbon::now();
                $year = in_array($now->month, [10, 11, 12]) ? $now->addYear()->format('Y') : $now->format('Y');
                return [$year => $year];
            });

            $view->with('users', $users);
            $view->with('years', $years);
            $view->with('days', $days);
            $view->with('schedules', $schedules);
            $view->with('tournaments', $tournaments);
        }
    }
}
