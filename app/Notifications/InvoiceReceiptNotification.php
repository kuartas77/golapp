<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\School;
use App\Service\Invoice\InvoicePdfService;
use App\Support\Invoice\InvoiceDocumentTerminology;
use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class InvoiceReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Invoice $invoice,
        private School $school,
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->invoice->loadMissing('inscription.player');
        $attachment = app(InvoicePdfService::class)->attachment($this->invoice);
        $documentLabel = InvoiceDocumentTerminology::singular($this->invoice);

        $message = (new MailMessage)
            ->subject("{$documentLabel} {$this->school->name}")
            ->greeting("Hola {$notifiable->names}")
            ->line("Adjuntamos el {$documentLabel} #{$this->invoice->invoice_number} de {$this->invoice->inscription->player->full_names}.")
            ->line('Este documento confirma que el pago fue completado.')
            ->attachData($attachment['content'], $attachment['filename'], [
                'mime' => $attachment['mime'],
            ]);

        SchoolMailFrom::apply($message, $this->school->name);

        return $message;
    }
}
