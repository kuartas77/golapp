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
        if ($request->ajax()) {
            return datatables()->of($this->repository->getAll())->toJson();
        }

        [$topicCategories, $topicGroups, $topicUniqueCodes, $topicCompetitionGroups] = $this->repository->getTopics();

        return view(
            'topic-notification.index',
            compact('topicCategories', 'topicGroups', 'topicUniqueCodes', 'topicCompetitionGroups')
        );
    }

    public function store(NotificationStoreRequest $request, TopicNotificationStoreService $service)
    {
        $service->store($request);
        return back();
    }
}
