<?php


namespace App\Http\ViewComposers\Assists;

use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
            } elseif(isInstructor()){
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false, auth()->id());
            }

            $months = Cache::remember("KEY_MONTHS", now()->addYear(), function () {
                return config('variables.KEY_MONTHS');
            });

            $view->with('months', $months);
            $view->with('yearMax', now()->year);
            $view->with('yearMin', now()->year);
            $view->with('training_groups', $training_groups);
        }
    }
}


