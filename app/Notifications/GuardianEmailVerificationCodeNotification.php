<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuardianEmailVerificationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        private readonly string $schoolName
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de verificación para tu inscripción')
            ->greeting('Verifica el correo del acudiente')
            ->line("Estás realizando una inscripción en {$this->schoolName}.")
            ->line("Tu código de verificación es: {$this->code}")
            ->line('El código vence en 10 minutos y solo puede usarse para esta inscripción.')
            ->line('Si no solicitaste este código, puedes ignorar este mensaje.');
    }
}
