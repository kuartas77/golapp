<?php

namespace App\Http\ViewComposers\TrainingSession;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Repositories\TrainingGroupRepository;
use App\Service\Groups\GroupCatalogCache;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TrainingSessionComposer
{
    public function __construct(private TrainingGroupRepository $trainingGroupRepository, private GroupCatalogCache $groupCatalogCache)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
    }

    public function compose(View $view)
    {
        if (Auth::check()) {
            $school_id = getSchool(auth()->user())->id;

            $instructorId = isInstructor() ? auth()->id() : null;
            $training_groups = $this->groupCatalogCache->remember(GroupCatalogCache::TRAINING, $school_id, 'training-session-options', function () use ($school_id, $instructorId) {
                $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);

                return $this->trainingGroupRepository->getListGroupsSchedule(false, $instructorId, $filter, $school_id)->pluck('full_schedule_group', 'id');
            }, $instructorId);

            $general = Cache::remember('KEY_TRAINING_SESSION_GENERAL_OBJECTIVE', now()->addMinutes(5), fn () => config('variables.KEY_TRAINING_SESSION_GENERAL_OBJECTIVE'));
            $specific = Cache::remember('KEY_TRAINING_SESSION_SPECIFIC_GOAL', now()->addMinutes(5), fn () => config('variables.KEY_TRAINING_SESSION_SPECIFIC_GOAL'));
            $content = Cache::remember('KEY_TRAINING_SESSION_CONTENT', now()->addMinutes(5), fn () => config('variables.KEY_TRAINING_SESSION_CONTENT'));
            $tasks = Cache::remember(
                'KEY_TRAINING_SESSION_TASKS',
                now()->addMinutes(5),
                function () use ($general, $specific, $content) {
                    $merged = array_merge(config('variables.KEY_TRAINING_SESSION_TASKS'), $general, $specific, $content);
                    sort($merged);
                    $tasks = [];
                    foreach ($merged as $value) {
                        $tasks[$value] = $value;
                    }

                    return $tasks;
                }
            );
            $numberTasks = range(1, 3); // TODO: setting by school

            $view->with('tasks', $tasks);
            $view->with('generals', $general);
            $view->with('contents', $content);
            $view->with('specifics', $specific);
            $view->with('numberTasks', $numberTasks);
            $view->with('training_groups', $training_groups);
        }
    }
}
