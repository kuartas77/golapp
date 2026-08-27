<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $schoolName = '';

    /**
     * Create a new notification instance.
     */
    public function __construct(private User $user, private string $pass, string $schoolName = '')
    {
        $this->schoolName = $schoolName;
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Notificación de Registro.')
            ->markdown('emails.register', [
                'user' => $this->user,
                'pass' => $this->pass,
            ]);

        SchoolMailFrom::apply($message, $this->schoolName);

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
