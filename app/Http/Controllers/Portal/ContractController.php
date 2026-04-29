<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Service\Contracts\ContractTemplateService;
use App\Traits\PDFTrait;
use Symfony\Component\HttpFoundation\Response;

class ContractController extends Controller
{
    use PDFTrait;

    public function __construct(
        private readonly ContractTemplateService $contractTemplateService
    ) {
    }

    public function show(School $school, string $contractTypeCode)
    {
        abort_unless($school->is_enable && $school->create_contract, Response::HTTP_NOT_FOUND);

        $data = $this->contractTemplateService->renderForSchool(
            $school,
            $contractTypeCode,
            $this->contractTemplateService->buildPreviewVariables($school)
        );

        abort_if($data === null, Response::HTTP_NOT_FOUND, 'La plantilla solicitada no esta disponible.');

        $this->setWatermarkSize($this->contractTemplateService->watermarkSize());
        $this->setConfigurationMpdf($this->contractTemplateService->pdfConfiguration());
        $this->createPDF($data, $this->contractTemplateService->pdfViewForCode($contractTypeCode), false);

        return $this->stream($this->contractTemplateService->fileLabelForCode($contractTypeCode) . '.pdf');
    }
}
