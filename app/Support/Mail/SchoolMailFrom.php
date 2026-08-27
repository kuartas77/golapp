<?php

declare(strict_types=1);

namespace App\Support\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

final class SchoolMailFrom
{
    public static function apply(MailMessage|Mailable $message, string $schoolName): void
    {
        $address = config('mail.from.address');
        $schoolName = trim($schoolName);

        if (is_string($address) && $address !== '' && $schoolName !== '') {
            $message->from($address, $schoolName);
        }
    }
}
