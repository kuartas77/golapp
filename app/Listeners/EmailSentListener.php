<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Notifications\InscriptionNotification;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;
use App\Modules\Inscriptions\Jobs\DeleteDocuments;

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
                //
                break;
        }
    }

    private function notificationInscription($inscription, $school)
    {
        dispatch(new DeleteDocuments($school->slug, (string)$inscription->unique_code))->delay(now()->addWeek());
    }
}
