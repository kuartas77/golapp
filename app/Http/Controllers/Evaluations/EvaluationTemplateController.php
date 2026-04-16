<?php

namespace App\Http\Controllers\Evaluations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluations\StoreEvaluationTemplateRequest;
use App\Http\Requests\Evaluations\UpdateEvaluationTemplateRequest;
use App\Http\Requests\Evaluations\UpdateEvaluationTemplateStatusRequest;
use App\Http\Resources\API\Evaluations\EvaluationTemplateResource;
use App\Models\Evaluations\EvaluationTemplate;
use App\Models\TrainingGroup;
use App\Service\Evaluations\EvaluationTemplateCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationTemplateController extends Controller
{
    private const STATUS_OPTIONS = [
        ['value' => 'draft', 'label' => 'Borrador'],
        ['value' => 'active', 'label' => 'Activa'],
        ['value' => 'inactive', 'label' => 'Inactiva'],
    ];

    private const STATUS_ACTION_OPTIONS = [
        ['value' => 'active', 'label' => 'Activar'],
        ['value' => 'inactive', 'label' => 'Inactivar'],
    ];

    private const SCORE_TYPE_OPTIONS = [
        ['value' => 'numeric', 'label' => 'Numérico'],
        ['value' => 'scale', 'label' => 'Escala'],
    ];

    public function __construct(
        private EvaluationTemplateCrudService $crudService
    ) {}

    public function options(): JsonResponse
    {
        $schoolId = $this->currentSchoolId();

        return response()->json([
            'filters' => [
                'years' => $this->yearOptions($schoolId),
                'statuses' => self::STATUS_OPTIONS,
                'training_groups' => $this->trainingGroupOptions($schoolId),
            ],
            'editor' => [
                'statuses' => self::STATUS_OPTIONS,
                'status_actions' => self::STATUS_ACTION_OPTIONS,
                'score_types' => self::SCORE_TYPE_OPTIONS,
                'training_groups' => $this->trainingGroupOptions($schoolId),
                'scale_options' => config('evaluations.scale_options.scale', []),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $schoolId = $this->currentSchoolId();
        $perPage = max(1, min(100, (int) $request->integer('per_page', 15)));
        $search = trim((string) $request->input('search'));

        $templates = EvaluationTemplate::query()
            ->where('school_id', $schoolId)
            ->with(['trainingGroup'])
            ->withCount(['criteria', 'playerEvaluations'])
            ->when($request->filled('year'), fn ($query) => $query->where('year', (int) $request->input('year')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('training_group_id'), function ($query) use ($request) {
                $trainingGroupId = $request->integer('training_group_id');
                $query->where('training_group_id', $trainingGroupId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('year')
            ->orderBy('name')
            ->orderByDesc('version')
            ->paginate($perPage);

        return response()->json([
            'data' => EvaluationTemplateResource::collection($templates->getCollection())->resolve(),
            'meta' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
                'from' => $templates->firstItem(),
                'to' => $templates->lastItem(),
            ],
        ]);
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

    private function trainingGroupOptions(int $schoolId): array
    {
        return TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (TrainingGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])
            ->values()
            ->all();
    }

    private function yearOptions(int $schoolId): array
    {
        $years = EvaluationTemplate::query()
            ->where('school_id', $schoolId)
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();

        if ($years->isEmpty()) {
            $years->push((int) now()->year);
        }

        return $years
            ->unique()
            ->sortDesc()
            ->values()
            ->map(fn (int $year) => ['value' => $year, 'label' => (string) $year])
            ->all();
    }
}
