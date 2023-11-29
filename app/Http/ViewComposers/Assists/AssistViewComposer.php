<?php


namespace App\Http\ViewComposers\Assists;

use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class AssistViewComposer
{
    /**
     * @var TrainingGroupRepository
     */
    private $trainingGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
    }

    public function compose(View $view)
    {
        if (Auth::check()) {

            if (isAdmin() || isSchool()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false);
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false, auth()->id());
            }

            $months = Cache::rememberForever("KEY_MONTHS", fn() => config('variables.KEY_MONTHS'));

            $actual_month = Str::ucfirst(Date::now()->monthName);

            $view->with('months', $months);
            $view->with('actual_month', $actual_month);
            $view->with('yearMax', now()->year);
            $view->with('yearMin', now()->year);
            $view->with('training_groups', $training_groups);
        }
    }
}


