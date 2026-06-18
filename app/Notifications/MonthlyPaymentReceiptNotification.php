<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Payment;
use App\Models\School;
use App\Service\Payment\MonthlyPaymentReceiptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyPaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Payment $payment,
        private string $month,
        private School $school
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->payment->loadMissing('inscription.player');

        $attachment = app(MonthlyPaymentReceiptService::class)
            ->receiptPdfAttachment($this->payment, $this->month, $this->school);

        $monthLabel = config("variables.KEY_INDEX_MONTHS_LABEL.{$this->month}", ucfirst($this->month));

        return (new MailMessage)
            ->subject("Recibo de mensualidad {$this->school->name}")
            ->greeting("Hola {$notifiable->names}")
            ->line("Adjuntamos el comprobante de pago de mensualidad de {$this->payment->inscription->player->full_names}.")
            ->line("Periodo: {$monthLabel} {$this->payment->year}.")
            ->line('Gracias por estar al día con la escuela.')
            ->attachData($attachment['content'], $attachment['filename'], [
                'mime' => $attachment['mime'],
            ]);
    }
}
