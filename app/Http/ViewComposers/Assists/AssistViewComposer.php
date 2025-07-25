<?php


namespace App\Http\ViewComposers\Assists;

use Closure;
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
    private TrainingGroupRepository $trainingGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
    }

    public function compose(View $view): void
    {
        if (Auth::check()) {
            $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
            if (isAdmin() || isSchool()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter)->pluck('full_schedule_group', 'id');
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter)->pluck('full_schedule_group', 'id');
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
