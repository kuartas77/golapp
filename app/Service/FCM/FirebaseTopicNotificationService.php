<?php

namespace App\Service\FCM;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;

class FirebaseTopicNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/notificaciones-golapp-firebase-adminsdk-fbsvc-59f943c55c.json'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken(string $token, array $data, ?string $title = null, ?string $body = null): array
    {
        try {
            // Construir el mensaje base
            $message = CloudMessage::new()
                ->withToken($token);

            // Agregar datos personalizados
            $message = $message->withData($data);

            // Agregar notificación (opcional - para cuando la app está en segundo plano)
            if ($title && $body) {
                $notification = Notification::create($title, $body);
                $message = $message->withNotification($notification);
            }

            // Enviar el mensaje
            $response = $this->messaging->send($message);

            logger("response-send-topic-fcm", [$response]);

            return [
                'success' => true,
                // 'message_id' => $response->id(),
                'token' => $token,
                'data' => $data
            ];

        } catch (MessagingException $e) {
            logger()->error('Firebase Topic Notification Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'token' => $token
            ];
        }
    }

    /**
     * Enviar notificación a un topic específico
     */
    public function sendToTopic(string $topic, array $data, ?string $title = null, ?string $body = null): array
    {
        try {
            // Construir el mensaje base
            $message = CloudMessage::new()
                ->withTopic($topic);

            // Agregar datos personalizados
            $message = $message->withData($data);

            // Agregar notificación (opcional - para cuando la app está en segundo plano)
            if ($title && $body) {
                $notification = Notification::create($title, $body);
                $message = $message->withNotification($notification);
            }

            // Enviar el mensaje
            $response = $this->messaging->send($message);

            logger("response-send-topic-fcm", [$response]);

            return [
                'success' => true,
                // 'message_id' => $response->id(),
                'topic' => $topic,
                'data' => $data
            ];

        } catch (MessagingException $e) {
            logger()->error('Firebase Topic Notification Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'topic' => $topic
            ];
        }
    }

    /**
     * Enviar notificación a múltiples topics
     */
    public function sendToTopics(array $topics, array $data, ?string $title = null, ?string $body = null): array
    {
        $results = [];

        foreach ($topics as $topic) {
            $results[$topic] = $this->sendToTopic($topic, $data, $title, $body);
        }

        return $results;
    }

    /**
     * Enviar notificación condicional a topic
     * Ejemplo: "'TopicA' in topics && 'TopicB' in topics"
     */
    public function sendToCondition(string $condition, array $data, ?string $title = null, ?string $body = null): array
    {
        try {
            $message = CloudMessage::new()->withCondition($condition)
                ->withData($data);

            if ($title && $body) {
                $notification = Notification::create($title, $body);
                $message = $message->withNotification($notification);
            }

            $response = $this->messaging->send($message);
            logger("response-send-condition-fcm", [$response]);

            return [
                'success' => true,
                // 'message_id' => $response->id(),
                'condition' => $condition,
                'data' => $data
            ];

        } catch (MessagingException $e) {
            logger()->error('Firebase Condition Notification Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'condition' => $condition
            ];
        }
    }

    /**
     * Suscribir dispositivos a un topic
     * Necesitas tener los registration tokens de los dispositivos
     */
    public function subscribeToTopic(string $topic, array $registrationTokens): array
    {
        try {
            $result = $this->messaging->subscribeToTopic($topic, $registrationTokens);

            logger("response-subscribeToTopic-fcm", [$result]);

            return [
                'success' => true,
                'topic' => $topic,
                // 'success_count' => count($result->successes()),
                // 'failure_count' => count($result->failures()),
                // 'errors' => $result->errors()
            ];

        } catch (MessagingException $e) {
            logger()->error('Firebase Subscribe Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'topic' => $topic
            ];
        }
    }

    /**
     * Desuscribir dispositivos de un topic
     */
    public function unsubscribeFromTopic(string $topic, array $registrationTokens): array
    {
        try {
            $result = $this->messaging->unsubscribeFromTopic($topic, $registrationTokens);

            logger("response-unsubscribeFromTopic-fcm", [$result]);

            return [
                'success' => true,
                'topic' => $topic,
                // 'success_count' => count($result->successes()),
                // 'failure_count' => count($result->failures()),
                // 'errors' => $result->errors()
            ];

        } catch (MessagingException $e) {
            logger()->error('Firebase Unsubscribe Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'topic' => $topic
            ];
        }
    }
}