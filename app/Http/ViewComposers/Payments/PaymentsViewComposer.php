<?php


namespace App\Http\ViewComposers\Payments;

use App\Models\Inscription;
use App\Models\Payment;
use App\Repositories\TrainingGroupRepository;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

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
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter)->pluck('full_schedule_group', 'id');
            } elseif (isInstructor()) {
                $training_groups = $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter)->pluck('full_schedule_group', 'id');
            }

            $categories = Inscription::where('year', now()->year)->distinct()->schoolId()->pluck('category', 'category');

            $years = Payment::query()->select('year')->distinct()->groupBy('year')->pluck('year', 'year')->toArray();
            $view->with('years', $years);
            $view->with('categories', $categories);
            $view->with('training_groups', ($training_groups ?? collect()));
        }
    }
}
