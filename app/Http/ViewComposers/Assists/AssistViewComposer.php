<?php


namespace App\Http\ViewComposers\Assists;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Models\Assist;
use App\Repositories\TrainingGroupRepository;
use Closure;
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
                $trainingGroups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter)->pluck('full_schedule_group', 'id');
                $trainingGroups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter);
            }

            $months = Cache::rememberForever("KEY_MONTHS", fn() => config('variables.KEY_MONTHS'));
            $monthsKeys = Cache::rememberForever("KEY_MONTHS_INDEX", fn() => config('variables.KEY_MONTHS_INDEX'));

            $actual_month = Str::ucfirst(Date::now()->monthName);

            $previousMonth = now()->subMonthNoOverflow();

            $view->with('months', $months);
            $view->with('months_keys', $monthsKeys);
            $view->with('actual_month', $actual_month);
            $view->with('yearMax', now()->year);
            $view->with('yearMin', now()->year);
            $view->with('training_groups', $training_groups);
            $view->with('trainingGroups', $trainingGroups);
            $view->with('years', Assist::schoolId()->distinct()->pluck('year', 'year'));
            $view->with('defaultYear', (int) $previousMonth->year);
            $view->with('defaultMonth', (int) $previousMonth->month);
        }
    }
}
