<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Closure;
use App\Traits\UploadFile;
use App\Traits\PDFTrait;
use App\Models\School;
use App\Models\Player;
use App\Models\Contract;

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

        if ($this->school->create_contract) {

            $this->uploadSigns($passable);

            $this->signContracts($passable);
        }

        $passable->setPaths($this->paths);

        return $next($passable);
    }

    private function makeDirectory(): void
    {
        $folderDocuments = $this->school->slug;
        $this->folderDocuments = $folderDocuments . DIRECTORY_SEPARATOR . $this->player->unique_code;
        Storage::createDirectory($folderDocuments);
    }

    private function uploadSigns(Passable $passable): void
    {
        if ($signatureTutor = $passable->getPropertyFromData('signatureTutor')) {
            $this->paths['sign_tutor'] = $this->uploadSignImage($signatureTutor, $this->folderDocuments);
        }

        if ($signatureAlumno = $passable->getPropertyFromData('signatureAlumno')) {
            $this->paths['sign_player'] = $this->uploadSignImage($signatureAlumno, $this->folderDocuments);
        }
    }

    private function signContracts(Passable $passable): void
    {
        $year = $passable->getPropertyFromData('year');
        $storagePath = "app" . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR;

        $fileContractPDF = $this->folderDocuments . DIRECTORY_SEPARATOR . sprintf('CONTRATO DE INSCRIPCIÓN %s.pdf', $year);
        $fileContractPDFPath = storage_path($storagePath . $fileContractPDF);
        $this->makeContract(1, $fileContractPDFPath);
        $this->paths['contract_one'] = ['CONTRATO DE INSCRIPCIÓN' => $fileContractPDFPath];

        if ($this->school->sign_player && isset($this->paths['sign_player'])) {
            $fileContractPDF = $this->folderDocuments . DIRECTORY_SEPARATOR . sprintf('CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA %s.pdf', $year);
            $fileContractPDFPath = storage_path($storagePath . $fileContractPDF);
            $this->makeContract(2, $fileContractPDFPath);
            $this->paths['contract_two'] = ['CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA' => $fileContractPDFPath];
        }
    }

    private function makeContract(int $documentOption, $filename): void
    {
        $data = $this->setParametersPdf($documentOption);

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
            case 1:
                $this->createPDF($data, 'contracts/contract_inscription.blade.php', false);
                break;
            case 2:
                $this->createPDF($data, 'contracts/contract_affiliate.blade.php', false);
                break;
        }

        $this->save($filename);
    }

    private function setParametersPdf(int $documentOption): array
    {
        $this->player->load(['people']);
        $people = $this->player->people;

        $variables = [];
        $variables['SCHOOL_LOGO']       = $this->school->logo_local;;
        $variables['SCHOOL_NAME']       = Str::upper($this->school->name);
        $variables['SCHOOL_NAMES']      = $variables['SCHOOL_NAME'];
        $variables['SCHOOL_AGENT']      = $this->school->agent;

        if (in_array($this->school->id, [5, 6, 7])) {
            $variables['SCHOOL_SIGN']   = storage_path("app/public/" . $this->school->slug . '/firma10+pro.jpg');
        }

        if ($this->school->id == 2) {
            $variables['IMAGE_ONE']     = storage_path("app/public/" . $this->school->slug . '/img-contract-1.jpg');
            $variables['IMAGE_TWO']     = storage_path("app/public/" . $this->school->slug . '/img-contract-2.jpg');
            $variables['IMAGE_THREE']   = storage_path("app/public/" . $this->school->slug . '/img-contract-3.jpg');
        }

        $variables['DAY']               = now()->format('d');
        $variables['MONTH']             = config('variables.KEY_MONTHS_INDEX')[now()->month];
        $variables['YEAR']              = now()->format('Y');
        $variables['DATE']              = now()->format('d-m-Y');

        $variables['SIGN_PLAYER']       = ($this->school->sign_player && isset($this->paths['sign_player'])) ? storage_path("app/public/" . $this->paths['sign_player']) : '';
        $variables['PLAYER_FULLNAMES']  = Str::upper($this->player->full_names);
        $variables['PLAYER_DOC']        = (string) $this->player->identification_document;
        $variables['PLAYER_DATE_BIRTH'] = (string) $this->player->date_birth;
        $variables['PLAYER_ADDRESS']    = (string) $this->player->address;
        $variables['PLAYER_EPS']        = (string) $this->player->eps;
        $variables['CATEGORY']          = (string) $this->player->category;

        $tutor = $people->firstWhere('tutor', true);
        $mother = $people->whereIn('relationship', ['15', '16'])->first();
        $dad = $people->whereIn('relationship', ['20', '21'])->first();

        $variables['TUTOR_NAME']        = data_get($tutor, 'names', '');
        $variables['TUTOR_DOC']         = data_get($tutor, 'identification_card', '');
        $variables['SIGN_TUTOR']        = isset($this->paths['sign_tutor']) ? storage_path("app/public/" . $this->paths['sign_tutor']) : '';

        $variables['MOTHER_NAMES']      = data_get($mother, 'names', '');
        $variables['MOTHER_MOBILE']     = data_get($mother, 'mobile', '');
        $variables['MOTHER_EMAIL']      = data_get($mother, 'email', '');

        $variables['DAD_NAMES']         = data_get($dad, 'names', '');
        $variables['DAD_MOBILE']        = data_get($dad, 'mobile', '');
        $variables['DAD_EMAIL']         = data_get($dad, 'email', '');

        return $this->getContract($documentOption, $variables);
    }

    private function getContract(int $documentOption, array $variables): array
    {
        $contract = Contract::where('contract_type_id', $documentOption)->firstWhere('school_id', $this->school->id);

        $params = explode(",", str_replace(['[', ']'], ['', ''], $contract->parameters));

        $header = $contract->header;
        $body = $contract->body;
        $footer = $contract->footer;

        foreach ($params as $param) {
            if (isset($variables[$param])) {
                $header = str_replace('[' . $param . ']', $variables[$param], $header);
                $body = str_replace('[' . $param . ']', $variables[$param], $body);
                $footer = str_replace('[' . $param . ']', $variables[$param], $footer);
            }
        }

        $data = [];
        $data['school'] = $this->school;
        $data['header'] = $header;
        $data['body'] = $body;
        $data['footer'] = $footer;

        return $data;
    }
}
