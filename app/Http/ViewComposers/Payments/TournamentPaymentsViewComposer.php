<?php

namespace App\Http\ViewComposers\Payments;

use App\Models\Tournament;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TournamentPaymentsViewComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {

            $school_id = getSchool(auth()->user())->id;

            $tournaments = Cache::remember("KEY_TOURNAMENT_{$school_id}", now()->addDay(), function () {
                return Tournament::query()->schoolId()->orderBy('name')->pluck('name', 'id');
            });

            $view->with('tournaments', $tournaments);
        }
    }
}
