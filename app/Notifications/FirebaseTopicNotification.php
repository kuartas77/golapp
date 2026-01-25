<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class FirebaseTopicNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $topic;
    protected $topics;
    protected $condition;
    protected $title;
    protected $body;
    protected $data = [];

    public function __construct()
    {
        //
    }

    public static function create(): self
    {
        return new static();
    }

    public function toTopic(string $topic): self
    {
        $this->topic = $topic;
        return $this;
    }

    public function toTopics(array $topics): self
    {
        $this->topics = $topics;
        return $this;
    }

    public function withCondition(string $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function withBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function via($notifiable): array
    {
        return ['firebase-topic'];
    }

    public function toFirebaseTopic($notifiable): array
    {
        $message = [
            'data' => $this->data
        ];

        if ($this->topic) {
            $message['topic'] = $this->topic;
        } elseif ($this->topics) {
            $message['topics'] = $this->topics;
        } elseif ($this->condition) {
            $message['condition'] = $this->condition;
        }

        if ($this->title) {
            $message['title'] = $this->title;
        }

        if ($this->body) {
            $message['body'] = $this->body;
        }

        return $message;
    }
}

// usage
// \Illuminate\Notifications\Notification::send(
//     new \Illuminate\Notifications\AnonymousNotifiable(),
//     \App\Notifications\FirebaseTopicNotification::create()
//         ->toTopic('breaking_news')
//         ->withTitle('Última Hora')
//         ->withBody('Noticia importante del día') // para sincronizar notificaciones ['action' => 'sync_notifications']
//         ->withData([
//             'news_id' => '123',
//             'type' => 'breaking',
//             'click_action' => 'OPEN_NEWS'
//         ])
// );