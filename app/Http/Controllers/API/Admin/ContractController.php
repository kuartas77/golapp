<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\UpsertContractTemplateRequest;
use App\Service\Contracts\ContractTemplateService;
use Illuminate\Http\JsonResponse;

class ContractController extends Controller
{
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
}
