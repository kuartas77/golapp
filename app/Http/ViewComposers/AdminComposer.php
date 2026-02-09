<?php

namespace App\Http\ViewComposers;

use App\Models\School;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminComposer
{
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $school_selected = '';
            $schools = [];
            $isSchool = 0;
            $user = auth()->user();
            $school = getSchool($user);

            if (isAdmin()) {
                $schools = Cache::remember('admin.schools', now()->addMinutes(5), fn() => School::query()->get());
                $school_selected = $this->setSchoolName($schools);
                $schools = $schools->pluck('name', 'id');

            }elseif(isSchool()){
                $isSchool = 1;

                if($multiple = $school->settings->get('MULTIPLE_SCHOOLS')){
                    $campusIds = json_decode($multiple);
                    array_push($campusIds, $user->school_id);
                    $schools = School::query()->whereIn('id', $campusIds)->get();
                    $school_selected = $this->setSchoolName($schools, 'school');
                    $schools = $schools->pluck('name', 'id');
                }else{
                    $school_selected = $school->name;
                }
            }elseif(isInstructor()){
                $isSchool = 1;
                $school_selected = $school->name;
            }

            $view->with('isSchool', $isSchool);
            $view->with('admin_schools', $schools);
            $view->with('school_selected', $school_selected);
            $view->with('settings', $school->settings);
        }
    }

    private function setSchoolName($schools, $prefix = 'admin')
    {
        $school_selected = '';
        $key = "{$prefix}.selected_school";
        $schoolSelected = request()->session()->get($key);
        if(!is_null($schoolSelected)){
            $school_selected = $schools->firstWhere('id', $schoolSelected)->name;
        }
        return $school_selected;
    }

}
