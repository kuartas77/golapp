<?php


namespace App\Http\ViewComposers\Assists;

use Jenssegers\Date\Date;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrainingGroupRepository;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;

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
            $filter = \Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
            if (isAdmin() || isSchool()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false, auth()->id(), $filter);
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
