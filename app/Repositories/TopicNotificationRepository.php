<?php

namespace App\Repositories;

use App\Models\Inscription;
use App\Models\TopicNotification;
use App\Service\Notification\TopicService;
use App\Traits\ErrorTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class TopicNotificationRepository
{
    use ErrorTrait;

    public function getPlayerNotifications()
    {
        $player = request()->user();
        $player->load('notifications');
        return $player->notifications;
    }

    public function getPlayerNotification($id)
    {
        $player = request()->user();
        $player->load('notifications');
        return $player->notifications->firstWhere('id', $id);
    }

    public function getAll()
    {
        return TopicNotification::query()->schoolId();
    }

    public function markRead($idNotification = null)
    {
        $id = request()->input('notificationId', $idNotification);
        $notification = TopicNotification::query()->findOrFail($id);
        $notification->player_topic_notification()->update(['is_read' => true]);
    }

    public function markReadAll()
    {
        $player = request()->user();
        $player->load('notifications');
        foreach ($player->notifications as $notification) {
            $this->markRead($notification->id);
        }
    }

    public function getNotificationByTopic(array $params)
    {
        return TopicNotification::query()
            ->where('school_id', $params['school_id'])
            ->when(
                is_array($params['topic']),
                fn($q) => $q->whereIn('topic', $params['topic']),
                fn($q) => $q->where('topic', $params['topic'])
            )
            ->whereRaw("created_at >= NOW() - INTERVAL 1 DAY");

    }

    public function getTopics()
    {
        return TopicService::generateTopicBySchool(auth()->user());
    }
}
