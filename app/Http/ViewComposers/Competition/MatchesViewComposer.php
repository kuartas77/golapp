<?php


namespace App\Http\ViewComposers\Competition;


use Carbon\Carbon;
use App\Models\Tournament;
use App\Models\CompetitionGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MatchesViewComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {

            $minYear = Cache::remember('KEY_MIN_YEAR', now()->addDay(), function () {
                return Carbon::parse(DB::table('games')->when(isSchool() || isInstructor(), fn($query) => $query->where('school_id', auth()->user()->school->id))->min('created_at'))->year;
            });
            $tournaments = Cache::remember('KEY_TOURNAMENT', now()->addDay(), function () {
                return Tournament::orderBy('name')->pluck('name', 'id');
            });

            $positions = Cache::remember('KEY_POSITIONS', now()->addYear(), function () {
                return config('variables.KEY_POSITIONS');
            });

            $played = Cache::rememberForever('KEY_TIME', function () {
                $played = collect();
                for ($i = 0; $i <= 90; ++$i) {
                    $played->put($i, "{$i} MIN");
                }
                return $played;
            });

            $scores = Cache::rememberForever('KEY_SCORE', function () {
                $scores = collect();
                for ($i = 0; $i <= 10; ++$i) {
                    $scores->put($i, $i);
                }
                return $scores;
            });

            $qualifications = Cache::rememberForever('KEY_SCORE', function () {
                $qualifications = collect();
                for ($i = 1; $i <= 5; ++$i) {
                    $qualifications->put($i, $i);
                }
                return $qualifications;
            });

            if (isAdmin()){
                $competitionGroups = CompetitionGroup::get()->pluck('full_name', 'id');
            }elseif(isSchool()){
                $competitionGroups = CompetitionGroup::query()->schoolId()->get()->pluck('full_name', 'id');
            }else{
                $competitionGroups = CompetitionGroup::query()->schoolId()->where('user_id', auth()->id())->get()->pluck('full_name', 'id');
            }

            $view->with('played', $played);
            $view->with('scores', $scores);
            $view->with('minYear', $minYear);
            $view->with('positions', $positions);
            $view->with('tournaments', $tournaments);
            $view->with('qualifications', $qualifications);
            $view->with('competitionGroups', $competitionGroups);
        }
    }
}
