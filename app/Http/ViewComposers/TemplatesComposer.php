<?php


namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TemplatesComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $school_id = auth()->user()->school_id;
            $optionAssist = Cache::remember("KEY_ASSIST_{$school_id}", now()->addYear(), function () {
                return config('variables.KEY_ASSIST');
            });
            $view->with('optionAssist', $optionAssist);
        }
    }
}
