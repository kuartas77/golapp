<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ReceivedPaymentReportNotification extends Notification
{
    use Queueable;

    public function __construct(private array $attachment) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Informe de pagos listo')
            ->greeting("Hola {$notifiable->name}")
            ->line('Adjuntamos el informe de pagos solicitado para todos los grupos.')
            ->attachData(
                $this->attachment['content'],
                $this->attachment['filename'],
                ['mime' => $this->attachment['mime']],
            );
    }
}
