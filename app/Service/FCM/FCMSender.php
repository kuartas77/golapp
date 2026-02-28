<?php

namespace App\Service\FCM;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FCMSender
{
    private Messaging $cloudMessaging;

    private function factory(): self
    {
        $factory = (new Factory)
        ->withServiceAccount(storage_path('app/notificaciones-golapp-firebase-adminsdk-fbsvc-59f943c55c.json'));

        $this->cloudMessaging = $factory->createMessaging();

        return $this;
    }

    private function makeMessage(string $topic): CloudMessage
    {
        $message = CloudMessage::new()
        ->withData(['action' => 'sync_notifications'])
        ->withNotification([
            'title' => 'sync all notifications',
            'body' => 'golapp-api'
        ])
        ->withTopic($topic);

        return $message;
    }

    public static function send(string $topic): void
    {
        $sender = new self;
        $message = $sender->factory()->makeMessage($topic);

        $sender->cloudMessaging->send($message);
    }
}
