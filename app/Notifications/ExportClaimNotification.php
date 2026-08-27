<?php

namespace App\Notifications;

use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportClaimNotification extends Notification
{
    use Queueable;

    public string $filename;

    private string $schoolName = '';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $filename, string $schoolName = '')
    {
        //
        $this->filename = $filename;
        $this->schoolName = $schoolName;
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)->subject('Archivo Exportado GOLAPP.')->greeting('Hola!')
            ->line("Adjunto se encuentra el archivo {$this->filename}.")
            ->attach(storage_path("app/public/exports/{$this->filename}"), [
                'as' => $this->filename,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        SchoolMailFrom::apply($message, $this->schoolName);

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
