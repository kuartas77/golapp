<?php

namespace App\Mail;

use App\Support\Mail\SchoolMailFrom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DuePayments extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public string $schoolName,
        public string $month,
        public $payments,
        public string $reportDate
    ) {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = $this->subject("Mensualidades en deuda - {$this->schoolName} - {$this->month}")
            ->markdown('emails.admin.due_payments');

        SchoolMailFrom::apply($message, $this->schoolName);

        return $message;
    }
}
