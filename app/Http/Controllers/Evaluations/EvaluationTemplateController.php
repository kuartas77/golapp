<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\StoreEvaluationTemplateRequest;
use App\Http\Requests\Evaluations\UpdateEvaluationTemplateRequest;
use App\Http\Requests\Evaluations\UpdateEvaluationTemplateStatusRequest;
use App\Http\Resources\API\Evaluations\EvaluationTemplateResource;
use App\Models\Evaluations\EvaluationTemplate;
use App\Service\Evaluations\EvaluationTemplateCrudService;
use App\Service\Evaluations\EvaluationTemplateReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationTemplateController extends Controller
{
    public function __construct(
        private EvaluationTemplateCrudService $crudService,
        private EvaluationTemplateReadService $reads
    ) {}

    public function options(): JsonResponse
    {
        return response()->json($this->reads->options($this->currentSchoolId()));
    }

    public function index(Request $request): JsonResponse
    {
        return $this->reads->datatable($request, $this->currentSchoolId());
    }

    public function store(StoreEvaluationTemplateRequest $request): JsonResponse
    {
        $template = $this->crudService->create(
            data: $request->validated(),
            schoolId: $this->currentSchoolId(),
            userId: (int) Auth::id()
        );

        $template->load(['trainingGroup', 'creator', 'criteria'])
            ->loadCount(['criteria', 'playerEvaluations']);

        return response()->json([
            'message' => 'Plantilla creada correctamente.',
            'data' => EvaluationTemplateResource::make($template)->resolve(),
        ], 201);
    }

    public function show(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $template = $this->scopedTemplate($evaluationTemplate);
        $template->load(['trainingGroup', 'creator', 'criteria'])
            ->loadCount(['criteria', 'playerEvaluations']);

        return response()->json([
            'data' => EvaluationTemplateResource::make($template)->resolve(),
        ]);
    }

    public function update(UpdateEvaluationTemplateRequest $request, EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $template = $this->scopedTemplate($evaluationTemplate);
        $template = $this->crudService->update($template, $request->validated());
        $template->load(['trainingGroup', 'creator', 'criteria'])
            ->loadCount(['criteria', 'playerEvaluations']);

        return response()->json([
            'message' => 'Plantilla actualizada correctamente.',
            'data' => EvaluationTemplateResource::make($template)->resolve(),
        ]);
    }

    public function duplicate(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $template = $this->scopedTemplate($evaluationTemplate);
        $copy = $this->crudService->duplicate($template, (int) Auth::id());
        $copy->load(['trainingGroup', 'creator', 'criteria'])
            ->loadCount(['criteria', 'playerEvaluations']);

        return response()->json([
            'message' => 'Se creó una nueva versión en borrador.',
            'data' => EvaluationTemplateResource::make($copy)->resolve(),
        ], 201);
    }

    public function updateStatus(
        UpdateEvaluationTemplateStatusRequest $request,
        EvaluationTemplate $evaluationTemplate
    ): JsonResponse {
        $template = $this->scopedTemplate($evaluationTemplate);
        $template = $this->crudService->updateStatus($template, $request->validated()['status']);
        $template->load(['trainingGroup', 'creator', 'criteria'])
            ->loadCount(['criteria', 'playerEvaluations']);

        return response()->json([
            'message' => 'Estado de la plantilla actualizado correctamente.',
            'data' => EvaluationTemplateResource::make($template)->resolve(),
        ]);
    }

    public function destroy(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $template = $this->scopedTemplate($evaluationTemplate);
        $this->crudService->delete($template);

        return response()->json([
            'message' => 'Plantilla eliminada correctamente.',
        ]);
    }

    private function scopedTemplate(EvaluationTemplate $template): EvaluationTemplate
    {
        abort_unless((int) $template->school_id === $this->currentSchoolId(), 404);

        return $template;
    }

    private function currentSchoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }
}
