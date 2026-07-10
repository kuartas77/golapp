<?php

namespace App\Repositories;

use App\Models\TopicNotification;
use App\Models\PlayerTopicNotification;
use App\Service\Notification\TopicService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TopicNotificationRepository
{
    public function getPlayerNotifications()
    {
        $player = request()->user();
        $player->load('notifications');
        return $player->notifications;
    }

    public function getPlayersNotifications($players): Collection
    {
        $playersById = $players->keyBy('id');

        return TopicNotification::query()
            ->select([
                'topic_notifications.*',
                'player_topic_notification.player_id as notification_player_id',
                'player_topic_notification.is_read as notification_is_read',
            ])
            ->join('player_topic_notification', 'topic_notifications.id', '=', 'player_topic_notification.topic_notification_id')
            ->whereIn('player_topic_notification.player_id', $playersById->keys())
            ->where('topic_notifications.created_at', '>=', now()->subDays(8))
            ->orderByDesc('topic_notifications.created_at')
            ->get()
            ->map(function (TopicNotification $notification) use ($playersById) {
                $playerId = (int) $notification->getAttribute('notification_player_id');
                $notification->setRelation('notificationPlayer', $playersById->get($playerId));
                $notification->setRelation('pivot', new PlayerTopicNotification([
                    'player_id' => $playerId,
                    'topic_notification_id' => $notification->id,
                    'is_read' => (bool) $notification->getAttribute('notification_is_read'),
                ]));

                return $notification;
            });
    }

    public function getPlayerNotification($id)
    {
        $player = request()->user();
        $player->load('notifications');
        return $player->notifications->firstWhere('id', $id);
    }

    public function getPlayersNotification($players, $id): TopicNotification
    {
        $notification = $this->getPlayersNotifications($players)->firstWhere('id', (int) $id);

        if (!$notification instanceof TopicNotification) {
            throw new ModelNotFoundException('Notification not found for guardian players');
        }

        return $notification;
    }

    public function getAll()
    {
        return TopicNotification::query()->schoolId();
    }

    public function markRead($idNotification = null)
    {
        $player = request()->user();
        $notification = $player->notifications()->whereKey($idNotification)->first();

        if (is_null($notification)) {
            throw new ModelNotFoundException('Notification not found for player');
        }

        $player->notifications()->updateExistingPivot($idNotification, ['is_read' => true]);
    }

    public function markReadForPlayer($player, int $notificationId): void
    {
        $notification = DB::table('player_topic_notification')
            ->where('player_id', $player->id)
            ->where('topic_notification_id', $notificationId)
            ->first();

        if (is_null($notification)) {
            throw new ModelNotFoundException('Notification not found for guardian player');
        }

        DB::table('player_topic_notification')
            ->where('player_id', $player->id)
            ->where('topic_notification_id', $notificationId)
            ->update(['is_read' => true]);
    }

    public function markReadForPlayers($players, int $notificationId): void
    {
        $query = DB::table('player_topic_notification')
            ->whereIn('player_id', $players->pluck('id'))
            ->where('topic_notification_id', $notificationId);

        if (!$query->exists()) {
            throw new ModelNotFoundException('Notification not found for guardian players');
        }

        $query->update(['is_read' => true]);
    }

    public function markReadAll()
    {
        $player = request()->user();
        $notificationIds = $player->notifications()->pluck('topic_notifications.id');

        foreach ($notificationIds as $notificationId) {
            $player->notifications()->updateExistingPivot($notificationId, ['is_read' => true]);
        }
    }

    public function markReadAllForPlayers($players): void
    {
        DB::table('player_topic_notification')
            ->whereIn('player_id', $players->pluck('id'))
            ->update(['is_read' => true]);
    }

    public function getNotificationByTopic(array $params)
    {
        $topics = is_array($params['topic']) ? $params['topic'] : [$params['topic']];

        return TopicNotification::query()
            ->where('school_id', $params['school_id'])
            ->where(function ($query) use ($topics) {
                foreach ($topics as $topic) {
                    $query->orWhere('topics', $topic)
                        ->orWhere('topics', 'like', "{$topic},%")
                        ->orWhere('topics', 'like', "%,{$topic},%")
                        ->orWhere('topics', 'like', "%,{$topic}");
                }
            })
            ->whereRaw("created_at >= NOW() - INTERVAL 1 DAY");

    }

    public function getTopics()
    {
        return TopicService::generateTopicBySchool(auth()->user());
    }
}
