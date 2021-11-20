<?php


namespace App\Http\ViewComposers\Competition;


use App\Models\CompetitionGroup;
use App\Models\Tournament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MatchesViewComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $tournaments = Cache::remember('KEY_TOURNAMENT', now()->addDay(), function () {
                return Tournament::orderBy('name')->pluck('name', 'id');
            });

            $positions = Cache::remember('KEY_POSITIONS', now()->addYear(), function () {
                return config('variables.KEY_POSITIONS');
            });

            if (auth()->user()->hasRole('administrador')){
                $competitionGroups = CompetitionGroup::get()
                    ->pluck('full_name', 'id');
            }else{
                $competitionGroups = CompetitionGroup::where('user_id', auth()->id())->get()
                    ->pluck('full_name', 'id');
            }

            $played = collect();
            for ($i = 0; $i <= 90; ++$i) {
                $played->put($i, "{$i} MIN");
            }

            $scores = collect();
            for ($i = 0; $i <= 10; ++$i) {
                $scores->put($i, $i);
            }

            $qualifications = collect();
            for ($i = 1; $i <= 5; ++$i) {
                $qualifications->put($i, $i);
            }

            $view->with('played', $played);
            $view->with('scores', $scores);
            $view->with('positions', $positions);
            $view->with('tournaments', $tournaments);
            $view->with('qualifications', $qualifications);
            $view->with('competitionGroups', $competitionGroups);
        }
    }
}
