<?php


namespace App\Http\ViewComposers\Payments;

use Closure;
use App\Models\Inscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\TrainingGroupRepository;

class PaymentsViewComposer
{
    /**
     * @var TrainingGroupRepository
     */
    private TrainingGroupRepository $trainingGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
    }

    public static function filterGroupsYearActive(Collection $groups): Collection|\Illuminate\Support\Collection
    {
        return $groups->filter(fn($group) => $group->year_active <= now()->year);
    }

    public function compose(View $view): void
    {
        if (Auth::check()) {
            $filter = Closure::fromCallable([$this, 'filterGroupsYearActive']);
            if (isSchool() || isAdmin()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter);
            }

            $categories = Inscription::where('year', now()->year)->distinct()->schoolId()->pluck('category', 'category');

            $view->with('yearMax', now()->year);
            $view->with('yearMin', now()->year);
            $view->with('categories', $categories);
            $view->with('training_groups', ($training_groups ?? collect()));
        }
    }
}
