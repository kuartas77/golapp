<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationStoreRequest;
use App\Repositories\TopicNotificationRepository;
use App\Service\Notification\TopicNotificationStoreService;
use Illuminate\Http\Request;

class TopicNotificationsController extends Controller
{
    public function __construct(private TopicNotificationRepository $repository)
    {
        //
    }

    public function index(Request $request)
    {
        if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
            return datatables()->of($this->repository->getAll())->toJson();
        }

        abort(404);
    }

    public function options()
    {
        [$topicCategories, $topicGroups, $topicUniqueCodes, $topicCompetitionGroups] = $this->repository->getTopics();

        return response()->json([
            'categories' => collect($topicCategories)->map(fn(array $item) => [
                'value' => $item['topic'],
                'label' => $item['name'],
            ])->values(),
            'training_groups' => collect($topicGroups)->map(fn(array $item) => [
                'value' => $item['topic'],
                'label' => $item['name'],
            ])->values(),
            'players' => collect($topicUniqueCodes)->map(fn(array $item) => [
                'value' => $item['search'],
                'label' => $item['name'],
            ])->values(),
            'competition_groups' => collect($topicCompetitionGroups)->map(fn(array $item) => [
                'value' => $item['topic'],
                'label' => $item['name'],
            ])->values(),
        ]);
    }

    public function store(NotificationStoreRequest $request, TopicNotificationStoreService $service)
    {
        $service->store($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
