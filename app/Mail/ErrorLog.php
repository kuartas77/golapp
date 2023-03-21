<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorLog extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $context;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $message, array $context)
    {
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.admin.error_report')->subject("GOLAPP - Error, Código: {$this->context['code']}");
    }
}
