<?php

namespace App\Notifications;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param $pass
     */
    public function __construct(private Inscription $inscription, private ?array $pathContracts = [])
    {
        $this->afterCommit();
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
    public function toMail($notifiable): MailMessage
    {
        $sendContracts = !is_null($this->pathContracts);

        $mailMessage = (new MailMessage)
            ->subject("{$this->inscription->school->name} Notificación de inscripción.")
            ->markdown('emails.inscriptions.added', ['inscription' => $this->inscription, 'sendContract' => $sendContracts]);

        if ($sendContracts) {
            foreach ($this->pathContracts as $key => $contract) {
                if (is_array($contract)) {
                    $key = array_keys($contract)[0];
                    $contract = array_values($contract)[0];
                }

                $mailMessage->attach($contract, [
                    'as' => "{$this->inscription->year}_{$this->inscription->unique_code}_{$key}.pdf",
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
