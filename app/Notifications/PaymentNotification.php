<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\School;
use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Payment $payment, private School $school)
    {
        //
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
        $notifyIndex = [2, 3];
        $message = (new MailMessage)
            ->subject("Notificación pagos de mensualidades {$this->school->name}.")
            ->markdown('emails.payments.debts', [
                'payment' => $this->payment,
                'school' => $this->school,
                'index' => $notifyIndex,
            ]);

        SchoolMailFrom::apply($message, $this->school->name);

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
