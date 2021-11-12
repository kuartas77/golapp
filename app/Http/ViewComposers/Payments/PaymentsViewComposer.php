<?php


namespace App\Http\ViewComposers\Payments;


use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PaymentsViewComposer
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
            if (auth()->user()->hasRole('administrador')) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false);
            } else {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false, auth()->id());
            }

            $view->with('yearMax', now()->year);
            $view->with('yearMin', now()->year);
            $view->with('training_groups', $training_groups);
        }
    }
}
