<?php


namespace App\Http\ViewComposers\Competition;


use Carbon\Carbon;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\CompetitionGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MatchesViewComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {


            $school_id = isAdmin() ? 0 : auth()->user()->school_id;

            $minYear = Cache::remember("KEY_MIN_YEAR_{$school_id}", now()->addDay(), function () use($school_id) {
                return Carbon::parse(Game::getMinYear($school_id))->year;
            });

            $tournaments = Cache::remember("KEY_TOURNAMENT_{$school_id}", now()->addDay(), function () {
                return Tournament::query()->schoolId()->orderBy('name')->pluck('name', 'id');
            });

            $positions = Cache::rememberForever("KEY_POSITIONS", function () {
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

            if (isAdmin() || isSchool()){
                $competitionGroups = CompetitionGroup::query()->schoolId()->get()->pluck('full_name_group', 'id');
            }else if(isInstructor()){
                $competitionGroups = CompetitionGroup::query()->schoolId()->where('user_id', auth()->id())->get()->pluck('full_name_group', 'id');
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
