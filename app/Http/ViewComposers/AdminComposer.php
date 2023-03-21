<?php

namespace App\Http\ViewComposers;

use App\Models\School;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AdminComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            
            $schools = [];
            if(isAdmin()){
                $schools = School::query()->pluck('name','id');
            }

            $view->with('admin_schools', $schools);
        }
    }
    
}
