<?php

namespace App\Listeners;

use App\Events\InscriptionAdded;
use App\Mail\SendInscriptionMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendInscriptionEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  InscriptionAdded  $event
     * @return void
     */
    public function handle(InscriptionAdded $event)
    {
        $inscription = $event->inscription;
        $inscription->load('peoples');
        $tutor = $inscription->peoples->where('is_tutor', true)->first();

        $emails = [$inscription->email];
        if (!empty($tutor)){
            $inscription->email === $tutor->email ?? array_push($emails, $tutor->email);
        }

        Mail::to($emails)->later(now()->addMinute(), new SendInscriptionMail($inscription));
    }
}
