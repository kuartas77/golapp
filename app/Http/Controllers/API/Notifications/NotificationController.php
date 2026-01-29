<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Notification\TopicNotification\TopicNotificationCollection;
use App\Http\Resources\API\Notification\TopicNotification\TopicNotificationResource;
use App\Models\TopicNotification;
use App\Repositories\TopicNotificationRepository;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private TopicNotificationRepository $repository)
    {
        //
    }

    public function index(): TopicNotificationCollection
    {
        return new TopicNotificationCollection($this->repository->getPlayerNotifications());
    }

    public function show($id): TopicNotificationResource
    {
        return new TopicNotificationResource($this->repository->getPlayerNotification($id));
    }

    public function read($id)
    {

    }

    public function readAll()
    {

    }


}
