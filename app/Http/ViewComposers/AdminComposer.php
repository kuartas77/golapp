<?php

namespace App\Http\ViewComposers;

use App\Models\School;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AdminComposer
{
    public function compose(View $view): void
    {
        if (Auth::check()) {

            $schools = [];
            $isSchool = 0;
            if (isAdmin()) {
                $schools = School::query()->pluck('name', 'id');
            }elseif(isSchool()){
                $user = auth()->user();
                $school = getSchool($user);
                $isSchool = 1;
                if($multiple = $school->settings->get('MULTIPLE_SCHOOLS')){
                    $campusIds = json_decode($multiple);
                    array_push($campusIds, $user->school_id);
                    $schools = School::query()->whereIn('id', $campusIds)->pluck('name', 'id');
                }
            }
            $view->with('isSchool', $isSchool);
            $view->with('admin_schools', $schools);
        }
    }

}
