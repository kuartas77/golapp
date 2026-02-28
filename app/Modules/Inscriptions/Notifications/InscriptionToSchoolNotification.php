<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Notifications;

use App\Jobs\DeleteTempZipAndPlayerFolder;
use App\Models\Inscription;
use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class InscriptionToSchoolNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private Inscription $inscription, private School $school)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject("Notificación de Registro.")
            ->markdown('emails.inscriptions.new_school', ['inscription' => $this->inscription, 'school' => $this->school, 'sendContract' => true]);

        [$zipAbsolute, $zipRelative, $playerFolder] = $this->attachment();

        $mailMessage->attach($zipAbsolute, [
            'as' => "{$this->inscription->unique_code}.zip",
            'mime' => 'application/zip',
        ]);

        DeleteTempZipAndPlayerFolder::dispatch($zipRelative, $playerFolder)
        ->onQueue('golapp_default') // opcional pero recomendado
        ->delay(now()->addMinutes(2));

        return $mailMessage;
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

    private function attachment()
    {
        $folderDocuments = $this->school->slug;
        $short = data_get($this->school, 'short_name', 'tmp');
        $playerFolder = 'tmp'. DIRECTORY_SEPARATOR .$folderDocuments . DIRECTORY_SEPARATOR . "{$short}-{$this->inscription->unique_code}";

        $zipName = $folderDocuments. '-'.$this->inscription->unique_code . '.zip';
        $zipRelative = 'tmp/zips/' . $zipName;

        Storage::disk('local')->makeDirectory('tmp/zips');
        $zipAbsolute = Storage::disk('local')->path($zipRelative);

        $zip = new \ZipArchive();

        if ($zip->open($zipAbsolute, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = Storage::disk('local')->files($playerFolder);

            foreach ($files as $file) {
                $absoluteFile = Storage::disk('local')->path($file);
                $zip->addFile($absoluteFile, basename($file));
            }

            $zip->close();
        }

        return [$zipAbsolute, $zipRelative, $playerFolder];
    }


}
