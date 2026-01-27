<?php

namespace App\Repositories;

use App\Models\TopicNotification;
use App\Traits\ErrorTrait;

class TopicNotificationRepository
{
    use ErrorTrait;

    public function getAll()
    {
        return TopicNotification::query()->schoolId();
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
}
