<?php

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Support\Facades\Storage;
use Closure;
use App\Traits\UploadFile;
use App\Traits\PDFTrait;
use App\Models\School;
use App\Models\Player;

final class CreateContractAction implements IContractPassable
{
    use PDFTrait;
    use UploadFile;

    private Player $player;
    private School $school;

    private string $folderDocuments;

    private array $paths = [];

    private array $tutor = [];

    public function handle(Passable $passable, Closure $next)
    {
        $this->school = $passable->getSchool();

        $this->player = $passable->getPlayer();

        $this->tutor = $passable->getTutor();

        $this->makeDirectory();

        $this->uploadSigns($passable);

        $this->signContract($passable);

        $passable->setPaths($this->paths);

        return $next($passable);
    }

    private function makeDirectory()
    {
        $folderDocuments = $this->school->slug;
        $this->folderDocuments = $folderDocuments . DIRECTORY_SEPARATOR . $this->player->unique_code;
        Storage::createDirectory("{$folderDocuments}");
    }

    private function uploadSigns(Passable $passable)
    {
        $this->paths['sign_tutor'] = $this->uploadSignImage($passable->getPropertyFromData('signatureTutor'), $this->folderDocuments);
        $this->paths['sign_player'] = $this->uploadSignImage($passable->getPropertyFromData('signatureAlumno'), $this->folderDocuments);
    }

    private function signContract(Passable $passable)
    {
        $year = $passable->getPropertyFromData('year');
        $storagePath = "app" . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR;

        $fileContractPDF = "{$this->folderDocuments}" . DIRECTORY_SEPARATOR . "CONTRATO DE INSCRIPCIÓN {$year}.pdf";
        $fileContractPDFPath = storage_path("{$storagePath}{$fileContractPDF}");
        $this->makeContract(1, $fileContractPDFPath);
        $this->paths['contract_one'] = ['CONTRATO DE INSCRIPCIÓN' => $fileContractPDFPath];

        $fileContractPDF = "{$this->folderDocuments}" . DIRECTORY_SEPARATOR . "CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA {$year}.pdf";
        $fileContractPDFPath = storage_path("{$storagePath}{$fileContractPDF}");
        $this->makeContract(2, $fileContractPDFPath);
        $this->paths['contract_two'] = ['CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA' => $fileContractPDFPath];
    }

    private function makeContract($documentOption, $filename)
    {
        $data = [];
        $data['school'] = $this->school;
        $data['tutor'] = $this->tutor;
        $data['player'] = $this->player;
        $data['sign_tutor'] = $this->paths['sign_tutor'];
        $data['sign_player'] = $this->paths['sign_player'];

        $this->setWatermarkSize([180, 180]);

        $this->setConfigurationMpdf([
            'format' => 'A4',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 35,
            'margin_bottom' => 20,
            'margin_header' => 4,
            'margin_footer' => 4,
        ]);

        switch ($documentOption) {
            case '1':
                $this->createPDF($data, 'contracts.contract_inscription.blade.php', false);
                break;
            case '2':
                $this->createPDF($data, 'contracts.contract_affiliate.blade.php', false);
                break;
        }

        $this->save($filename);
    }
}
