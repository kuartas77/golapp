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
            $optionAssist = Cache::remember("KEY_ASSIST", now()->addYear(), function () {
                return config('variables.KEY_ASSIST');
            });
            $view->with('optionAssist', $optionAssist);
        }
    }
}
