<?php

namespace App\Notifications;

use App\Models\People;
use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

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
        $schoolNames = $this->schoolNames();

        $resetUrl = url('/portal/acudientes/restablecer').'?'.http_build_query([
            'token' => $this->token,
            'email' => $this->guardian->email,
        ]);

        $subject = $this->isInvitation
            ? 'Activa tu acceso al portal de acudientes'
            : 'Restablece tu acceso al portal de acudientes';

        $line = $this->isInvitation
            ? 'Tu cuenta de acudiente fue creada. Define tu contraseña para activar el acceso al portal.'
            : 'Recibimos una solicitud para restablecer tu contraseña del portal de acudientes.';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Hola {$this->guardian->names},")
            ->line($line)
            ->when($schoolNames->isNotEmpty(), fn (MailMessage $message) => $message->line("Escuela: {$schoolNames->implode(', ')}"))
            ->action($this->isInvitation ? 'Definir contraseña' : 'Restablecer contraseña', $resetUrl)
            ->line('Si no reconoces esta solicitud, puedes ignorar este mensaje.');

        if ($schoolNames->count() === 1) {
            SchoolMailFrom::apply($message, (string) $schoolNames->first());
        }

        return $message;
    }

    private function schoolNames(): Collection
    {
        $this->guardian->loadMissing('players.schoolData');

        return $this->guardian->players
            ->pluck('schoolData.name')
            ->filter()
            ->unique()
            ->values();
    }
}
