<?php

namespace App\Notifications;

use App\Models\School;
use App\Models\User;
use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserPasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private User $user,
        private string $token
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url('/ingreso/restablecer-contrasena').'?'.http_build_query([
            'token' => $this->token,
            'email' => $this->user->email,
        ]);

        $message = (new MailMessage)
            ->subject('Restablece tu contraseña de GOLAPP')
            ->markdown('emails.auth.user-password-reset', [
                'user' => $this->user,
                'resetUrl' => $resetUrl,
            ]);

        $school = School::query()->find($this->user->school_id);

        if ($school instanceof School) {
            SchoolMailFrom::apply($message, $school->name);
        }

        return $message;
    }
}
