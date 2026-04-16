<?php

namespace App\Notifications;

use App\Models\People;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuardianPasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private People $guardian,
        private string $token,
        private bool $isInvitation = false
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url('/portal/acudientes/restablecer') . '?' . http_build_query([
            'token' => $this->token,
            'email' => $this->guardian->email,
        ]);

        $subject = $this->isInvitation
            ? 'Activa tu acceso al portal de acudientes'
            : 'Restablece tu acceso al portal de acudientes';

        $line = $this->isInvitation
            ? 'Tu cuenta de acudiente fue creada. Define tu contraseña para activar el acceso al portal.'
            : 'Recibimos una solicitud para restablecer tu contraseña del portal de acudientes.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hola {$this->guardian->names},")
            ->line($line)
            ->action($this->isInvitation ? 'Definir contraseña' : 'Restablecer contraseña', $resetUrl)
            ->line('Si no reconoces esta solicitud, puedes ignorar este mensaje.');
    }
}
