<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Support\Facades\Storage;
use Closure;
use App\Service\Contracts\ContractTemplateService;
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

    public function __construct(
        private readonly ContractTemplateService $contractTemplateService
    ) {
    }

    public function handle(Passable $passable, Closure $next)
    {
        $this->school = $passable->getSchool();

        $this->player = $passable->getPlayer();
        $this->player->loadMissing('people');

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
        $base = 'tmp'. DIRECTORY_SEPARATOR .$this->school->slug;
        $short = data_get($this->school, 'short_name', 'tmp');
        $folderPlayer = "{$short}-{$this->player->unique_code}";
        $this->folderDocuments = trim($base, "/\\") . DIRECTORY_SEPARATOR . $folderPlayer;
        Storage::disk('local')->makeDirectory($this->folderDocuments);
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
        $variables = $this->contractTemplateService->buildPlayerVariables($this->school, $this->player, $this->paths);

        foreach ($this->contractTemplateService->availablePortalContracts($this->school) as $contractDefinition) {
            $code = $contractDefinition['code'];

            if (($contractDefinition['requires_player_signature'] ?? false) && !isset($this->paths['sign_player'])) {
                continue;
            }

            $rendered = $this->contractTemplateService->renderForSchool($this->school, $code, $variables);

            if ($rendered === null) {
                continue;
            }

            $relativePath = $this->folderDocuments . DIRECTORY_SEPARATOR . sprintf(
                '%s %s.pdf',
                $this->contractTemplateService->fileLabelForCode($code),
                $year
            );
            $absolutePath = Storage::disk('local')->path($relativePath);

            $this->setWatermarkSize($this->contractTemplateService->watermarkSize());
            $this->setConfigurationMpdf($this->contractTemplateService->pdfConfiguration());
            $this->createPDF($rendered, $this->contractTemplateService->pdfViewForCode($code), false);
            $this->save($absolutePath);

            $this->paths['contracts'][$code] = [
                $this->contractTemplateService->fileLabelForCode($code) => $relativePath,
            ];
        }
    }
}
