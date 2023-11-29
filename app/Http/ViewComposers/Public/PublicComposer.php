<?php

namespace App\Http\ViewComposers\Public;

use App\Models\School;
use Illuminate\Contracts\View\View;

class PublicComposer
{
    public function compose(View $view)
    {
        $schools = School::query()->where('id','<>', 1)->where('is_enable', true)->pluck('name','slug');
        $view->with('public_schools', $schools);
    }

}
