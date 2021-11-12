<?php


namespace App\Http\ViewComposers\Payments;

use App\Models\Payment;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HistoricViewComposer
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
            $view->with('yearMax', Payment::select('year')->max('year') - 1);
            $view->with('yearMin', Payment::select('year')->min('year'));
            $view->with('training_groups', $this->trainingGroupRepository->getListGroupsSchedule(true));
        }
    }
}
