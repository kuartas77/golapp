<?php


namespace App\Http\ViewComposers\Payments;

use App\Models\Inscription;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
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
            $filter = \Closure::fromCallable([$this, 'filterGroupsYearActive']);
            if (isSchool() || isAdmin()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(false, auth()->id(), $filter);
            }

            $categories = Inscription::where('year', now()->year)->distinct()->schoolId()->pluck('category', 'category');

            $view->with('yearMax', now()->year);
            $view->with('yearMin', now()->year);
            $view->with('categories', $categories);
            $view->with('training_groups', ($training_groups ?? collect()));
        }
    }

    public static function filterGroupsYearActive(Collection $groups)
    {
        return $groups->filter(fn($group) => $group->year_active <= now()->year);
    }
}
