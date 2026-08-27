<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ReceivedPaymentReportNotification extends Notification
{
    use Queueable;

    private string $schoolName = '';

    public function __construct(private array $attachment, string $schoolName = '')
    {
        $this->schoolName = $schoolName;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Informe de pagos listo')
            ->greeting("Hola {$notifiable->name}")
            ->line('Adjuntamos el informe de pagos solicitado para todos los grupos.')
            ->attachData(
                $this->attachment['content'],
                $this->attachment['filename'],
                ['mime' => $this->attachment['mime']],
            );

        SchoolMailFrom::apply($message, $this->schoolName);

        return $message;
    }
}
