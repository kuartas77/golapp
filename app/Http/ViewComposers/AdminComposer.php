<?php

namespace App\Http\ViewComposers;

use App\Models\PaymentRequest;
use App\Models\School;
use App\Models\UniformRequest;
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

            $notificationPRs = Cache::remember(
                "admin.notification.payment_request.{$school->id}", 10,
                fn() => PaymentRequest::query()->where('school_id', $school->id)->whereHas('invoice', fn($q) => $q->where('status', '<>', 'paid'))->count()
            );

            $notificationURs = Cache::remember(
                "admin.notification.uniform_request.{$school->id}", 10,
                fn() => UniformRequest::query()->where('school_id', $school->id)->where('status', 'PENDING')->count()
            );

            $view->with('isSchool', $isSchool);
            $view->with('admin_schools', $schools);
            $view->with('school_selected', $school_selected);
            $view->with('settings', $school->settings);
            $view->with('notification_prs', $notificationPRs);
            $view->with('notification_urs', $notificationURs);
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
