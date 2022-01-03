<?php

namespace App\Listeners;

use App\Events\InscriptionAdded;
use App\Mail\InscriptionMail;
use App\Mail\SendInscriptionMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendInscriptionEmail implements ShouldQueue
{
    use InteractsWithQueue;

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
        DB::afterCommit(function() use($event){
            Mail::to([$event->inscription->email])->queue((new InscriptionMail($event->inscription))->onQueue('emails'));
        });
    }
}
