<?php


namespace App\Http\ViewComposers\Assists;

use App\Models\Assist;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class AssistHistoricViewComposer
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

            $months = Cache::rememberForever("KEY_MONTHS", function () {
                return config('variables.KEY_MONTHS');
            });

            $view->with('months', $months);
            $view->with('yearMax', Assist::query()->select('year')->schoolId()->max('year') - 1);
            $view->with('yearMin', Assist::query()->select('year')->schoolId()->min('year'));
            $view->with('training_groups', $this->trainingGroupRepository->getListGroupsSchedule(true));
        }
    }
}


