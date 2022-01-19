<?php

namespace App\Notifications;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Inscription $inscription;
    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param $pass
     */
    public function __construct(Inscription $inscription)
    {
        $this->afterCommit();
        $this->inscription = $inscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Notificación de Registro.")
            ->markdown('emails.inscriptions.added', ['inscription' => $this->inscription]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
