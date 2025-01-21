<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Closure;
use App\Notifications\InscriptionNotification;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;
use App\Modules\Inscriptions\Jobs\DeleteDocuments;
use App\Modules\Inscriptions\Actions\Create\Passable;
use App\Models\School;
use App\Models\Player;
use App\Models\Inscription;

final class SendDocumentsAction implements IContractPassable
{
    private Player $player;

    private School $school;

    private Inscription $inscription;

    private array $attributes = [];

    private array $paths = [];

    public function handle(Passable $passable, Closure $next)
    {
        $this->school = $passable->getSchool();

        $this->player = $passable->getPlayer();

        $this->inscription = $passable->getInscription();

        $this->paths = $passable->getPaths();

        if ($this->school->send_documents) {

            $this->setAttributes($passable);

            $this->storeDocumentsLocal($passable->getPropertyFromData('year'), (string)$this->player->unique_code);

            $this->sendDocumentsToSchool();
        }

        $this->sendNotification($passable);

        return $next($passable);
    }

    public function setAttributes(Passable $passable): void
    {
        $this->attributes['player_document'] = $passable->getPropertyFromData('player_document');
        $this->attributes['medical_certificate'] = $passable->getPropertyFromData('medical_certificate');
        $this->attributes['tutor_document'] = $passable->getPropertyFromData('tutor_document');
        $this->attributes['payment_receipt'] = $passable->getPropertyFromData('payment_receipt');
    }

    public function storeDocumentsLocal(string $year, string $uniqueCode): void
    {
        $folderDocuments = $this->school->slug . DIRECTORY_SEPARATOR . $uniqueCode;

        foreach (Inscription::$documentFields as $field) {

            if (!isset($this->attributes[$field])) {
                continue;
            }

            $file = $this->attributes[$field];

            $extension = $file->getClientOriginalExtension();

            $filename = sprintf('%s_[NAME].%s', $year, $extension);

            switch ($field) {
                case 'player_document':
                    $name = "DOC_IDENTIDAD";
                    break;
                case 'medical_certificate':
                    $name = 'CERT_MEDICO';
                    break;
                case 'tutor_document':
                    $name = 'CEDULA_ACUDIENTE';
                    break;
                case 'payment_receipt':
                    $name = 'RECIBO_PAGO';
                    break;
            }

            $destinationFile = str_replace(['[NAME]'], [$name], $filename);

            $this->paths[$field] = Storage::putFileAs($folderDocuments, $file, $destinationFile, 'public');
        }
    }

    private function sendDocumentsToSchool(): void
    {
        if ($this->paths !== []) {
            $school = $this->school;
            $destinations = [];
            $destinations[$school['email']] = $school['name'];
            Notification::route('mail', $destinations)->notify(
                (new InscriptionToSchoolNotification($this->inscription, $this->school))->onQueue('emails')
            );
        }
    }

    private function sendNotification(Passable $passable): void
    {
        $destinations = [];
        $playerMail = data_get($this->player, 'email');
        $tutorMail = $passable->getPropertyFromData('tutor_email');

        if (checkEmail($playerMail)) {
            $destinations[$playerMail] = $this->player->name . ' ' . $this->player->last_names;
        }

        if (checkEmail($tutorMail) && $tutorMail !== $playerMail) {
            $destinations[$tutorMail] = $passable->getPropertyFromData('tutor_name');
        }

        if ($destinations !== []) {

            $contracts = $this->school->create_contract ? [$this->paths['contract_one']] : [];

            Notification::route('mail', $destinations)->notify(
                (new InscriptionNotification($this->inscription, $contracts))->onQueue('emails')
            );

            dispatch(new DeleteDocuments($this->school->slug, (string)$this->player->unique_code))->delay(now()->addDay())->onQueue('emails');
        }
    }
}
