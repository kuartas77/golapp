<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Notifications;

use Illuminate\Support\Facades\File;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use App\Models\Inscription;
use App\Models\School;

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

        $pathFile = $this->attachment();

        $mailMessage->attach($pathFile);

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

    private function attachment(): string
    {
        $folderDocuments = $this->school->slug;
        $storagePath = "app".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR;
        $folder = $folderDocuments . DIRECTORY_SEPARATOR . $this->inscription->unique_code;
        $fileName = $this->inscription->unique_code . '.zip';
        logger($fileName);
        $zipArchive = new \ZipArchive();

        $tmpFilePath = sys_get_temp_dir() . '/' . $fileName;

        if ($zipArchive->open($tmpFilePath, \ZipArchive::CREATE)== TRUE)
        {
            $files = File::files(storage_path($storagePath . $folder));

            foreach ($files as $file){
                $relativeName = basename($file->getRelativePathname());
                $zipArchive->addFile($file->getPathname(), $relativeName);
            }

            $zipArchive->close();
        }

        return $tmpFilePath;
    }
}
