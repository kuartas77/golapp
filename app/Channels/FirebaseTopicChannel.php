<?php

namespace App\Channels;

use App\Service\FCM\FirebaseTopicNotificationService;
use Illuminate\Notifications\Notification;

class FirebaseTopicChannel
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseTopicNotificationService();
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // Verificar si la notificación tiene el método toFirebaseTopic
        if (!method_exists($notification, 'toFirebaseTopic')) {
            $className = get_class($notification);
            throw new \RuntimeException(
                "La notificación {$className} debe implementar el método toFirebaseTopic"
            );
        }

        // Obtener los datos de la notificación
        $message = $notification->toFirebaseTopic($notifiable);

        // Validar que el mensaje sea un array
        if (!is_array($message)) {
            throw new \InvalidArgumentException(
                'El método toFirebaseTopic debe retornar un array'
            );
        }

        // Validar que tenga al menos un destino
        if (!isset($message['topic']) && !isset($message['topics']) && !isset($message['condition'])) {
            throw new \InvalidArgumentException(
                'La notificación debe definir al menos un topic, topics o condition'
            );
        }

        // Enviar la notificación
        if (isset($message['token'])) {
            return $this->sendToToken($message);
        } elseif (isset($message['topic'])) {
            return $this->sendToSingleTopic($message);
        } elseif (isset($message['topics']) && is_array($message['topics'])) {
            return $this->sendToMultipleTopics($message);
        } elseif (isset($message['condition'])) {
            return $this->sendToCondition($message);
        }

        return ['success' => false, 'error' => 'Destino de notificación no válido'];
    }

    protected function sendToToken(array $message): array
    {
        return $this->firebaseService->sendToToken(
            $message['token'],
            $message['data'] ?? [],
            $message['title'] ?? null,
            $message['body'] ?? null
        );
    }

    /**
     * Enviar a un solo topic
     */
    protected function sendToSingleTopic(array $message): array
    {
        return $this->firebaseService->sendToTopic(
            $message['topic'],
            $message['data'] ?? [],
            $message['title'] ?? null,
            $message['body'] ?? null
        );
    }

    /**
     * Enviar a múltiples topics
     */
    protected function sendToMultipleTopics(array $message): array
    {
        return $this->firebaseService->sendToTopics(
            $message['topics'],
            $message['data'] ?? [],
            $message['title'] ?? null,
            $message['body'] ?? null
        );
    }

    /**
     * Enviar por condición
     */
    protected function sendToCondition(array $message): array
    {
        return $this->firebaseService->sendToCondition(
            $message['condition'],
            $message['data'] ?? [],
            $message['title'] ?? null,
            $message['body'] ?? null
        );
    }
}
