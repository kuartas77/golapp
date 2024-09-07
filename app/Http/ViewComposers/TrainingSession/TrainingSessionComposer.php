<?php

namespace App\Http\ViewComposers\TrainingSession;

use Closure;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrainingGroupRepository;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;

class TrainingSessionComposer
{
    public function __construct(private TrainingGroupRepository $trainingGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
    }

    public function compose(View $view)
    {
        if (Auth::check()) {
            $school_id = getSchool(auth()->user())->id;

            $training_groups = Cache::remember("KEY_TRAINING_GROUPS_{$school_id}", now()->addDay(), function () {
                $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
                return $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            });

            $general = Cache::remember("KEY_TRAINING_SESSION_GENERAL_OBJECTIVE", now()->addDays(8), fn () => config('variables.KEY_TRAINING_SESSION_GENERAL_OBJECTIVE'));
            $specific = Cache::remember("KEY_TRAINING_SESSION_SPECIFIC_GOAL", now()->addDays(8), fn () => config('variables.KEY_TRAINING_SESSION_SPECIFIC_GOAL'));
            $content = Cache::remember("KEY_TRAINING_SESSION_CONTENT", now()->addDays(8), fn () => config('variables.KEY_TRAINING_SESSION_CONTENT'));
            $tasks = Cache::remember(
                "KEY_TRAINING_SESSION_TASKS",
                // now()->addDays(8),
                1,
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

            $view->with('tasks', $tasks);
            $view->with('generals', $general);
            $view->with('contents', $content);
            $view->with('specifics', $specific);
            $view->with('training_groups', $training_groups);
        }
    }
}
