<?php


namespace App\Http\ViewComposers\Assists;

use App\Models\Assist;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

            $months = Cache::remember("KEY_MONTHS", now()->addYear(), function () {
                return config('variables.KEY_MONTHS');
            });

            $view->with('months', $months);
            $view->with('yearMax', Assist::select('year')->max('year') - 1);
            $view->with('yearMin', Assist::select('year')->min('year'));
            $view->with('training_groups', $this->trainingGroupRepository->getListGroupsSchedule(true));
        }
    }
}


