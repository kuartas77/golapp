<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Notifications\InscriptionNotification;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;

class EmailSentListener
{
    /**
     * Handle the event.
     *
     * @param  MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        // Email was sent successfully
        $notification = (string) data_get($event->data, '__laravel_notification', '');

        switch ($notification) {
            case InscriptionNotification::class:
            case InscriptionToSchoolNotification::class:
                $inscription = data_get($event->data, 'inscription');
                $school = data_get($inscription, 'school');
                $this->notificationInscription($inscription, $school);
                break;

            default:
                # code...
                break;
        }

        logger("email send", [$notification]);
    }

    private function notificationInscription($inscription, $school)
    {
        logger("message send", [
            'unique_code' => $inscription->unique_code,
            'school_id' => $school->id
        ]);
    }
}
