<?php

namespace App\Notifications;

use App\Models\Player;
use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterPlayerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Player $player;

    public function __construct(Player $player)
    {
        $this->afterCommit();
        $this->player = $player->load('schoolData');
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
        $schoolName = (string) data_get($this->player, 'schoolData.name', '');

        $message = (new MailMessage)
            ->subject('Notificación Deportista Registrado')
            ->markdown('emails.player-register', ['player' => $this->player]);

        SchoolMailFrom::apply($message, $schoolName);

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
