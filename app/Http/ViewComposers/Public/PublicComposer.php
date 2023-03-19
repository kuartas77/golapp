<?php

namespace App\Http\ViewComposers\Public;

use App\Models\School;
use Illuminate\Contracts\View\View;

class PublicComposer
{
    public function compose(View $view)
    {
        $view->with('public_schools', School::query()->where('id','<>', 1)->pluck('name','slug'));
    }
    
}
