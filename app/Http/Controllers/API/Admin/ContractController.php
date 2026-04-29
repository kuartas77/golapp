<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\UpsertContractTemplateRequest;
use App\Service\Contracts\ContractTemplateService;
use App\Traits\PDFTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContractController extends Controller
{
    use PDFTrait;

    public function __construct(
        private readonly ContractTemplateService $contractTemplateService
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json(
            $this->contractTemplateService->editorPayload(getSchool(auth()->user()))
        );
    }

    public function update(UpsertContractTemplateRequest $request, string $contractTypeCode): JsonResponse
    {
        $school = getSchool(auth()->user());
        $this->contractTemplateService->upsertSchoolContract(
            $school,
            $contractTypeCode,
            $request->validated()
        );

        return response()->json([
            'message' => 'Contrato guardado correctamente.',
            'data' => $this->contractTemplateService->editorTypePayload($school, $contractTypeCode),
        ]);
    }

    public function preview(string $contractTypeCode)
    {
        $school = getSchool(auth()->user());

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
