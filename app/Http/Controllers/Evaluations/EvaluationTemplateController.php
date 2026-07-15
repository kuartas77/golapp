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
        $search = trim((string) ($request->input('template_search') ?? data_get($request->input('search'), 'value', '')));

        $query = EvaluationTemplate::query()
            ->select('evaluation_templates.*')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'evaluation_templates.training_group_id')
            ->where('evaluation_templates.school_id', $schoolId)
            ->with(['trainingGroup'])
            ->withCount(['criteria', 'playerEvaluations']);

        return datatables()->eloquent($query)
            ->filter(function ($query) use ($request, $search) {
                $query->when($request->filled('year'), fn ($q) => $q->where('evaluation_templates.year', (int) $request->input('year')))
                    ->when($request->filled('status'), fn ($q) => $q->where('evaluation_templates.status', $request->input('status')))
                    ->when($request->filled('training_group_id'), function ($q) use ($request) {
                        $q->where('evaluation_templates.training_group_id', $request->integer('training_group_id'));
                    });

                if ($search === '') {
                    return;
                }

                $like = "%{$search}%";
                $query->where(function ($nestedQuery) use ($like) {
                    $nestedQuery->where('evaluation_templates.name', 'like', $like)
                        ->orWhere('evaluation_templates.description', 'like', $like)
                        ->orWhere('evaluation_templates.status', 'like', $like)
                        ->orWhere('evaluation_templates.year', 'like', $like)
                        ->orWhere('training_groups.name', 'like', $like);
                });
            })
            ->orderColumn('name', 'evaluation_templates.name $1')
            ->orderColumn('training_group_name', 'training_groups.name $1')
            ->orderColumn('year', 'evaluation_templates.year $1')
            ->orderColumn('version', 'evaluation_templates.version $1')
            ->orderColumn('status', 'evaluation_templates.status $1')
            ->orderColumn('updated_at', 'evaluation_templates.updated_at $1')
            ->addColumn('training_group_name', fn (EvaluationTemplate $template) => $template->trainingGroup?->name)
            ->editColumn('created_at', fn (EvaluationTemplate $template) => $template->created_at?->toISOString())
            ->editColumn('updated_at', fn (EvaluationTemplate $template) => $template->updated_at?->toISOString())
            ->addColumn('criteria_count', fn (EvaluationTemplate $template) => (int) ($template->criteria_count ?? 0))
            ->addColumn('evaluations_count', fn (EvaluationTemplate $template) => (int) ($template->player_evaluations_count ?? 0))
            ->addColumn('is_in_use', fn (EvaluationTemplate $template) => $template->isInUse())
            ->addColumn('is_editable', fn (EvaluationTemplate $template) => !$template->isInUse())
            ->addColumn('can_delete', fn (EvaluationTemplate $template) => !$template->isInUse())
            ->addColumn('can_duplicate', fn () => true)
            ->addColumn('can_activate', fn (EvaluationTemplate $template) => $template->status !== 'active')
            ->addColumn('can_inactivate', fn (EvaluationTemplate $template) => $template->status !== 'inactive')
            ->toJson();
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
            ->get(['id', 'name', 'is_complementary'])
            ->map(function (TrainingGroup $group) {
                $label = $group->is_complementary
                    ? "{$group->name} (Complementario)"
                    : $group->name;

                return [
                'id' => $group->id,
                'name' => $group->name,
                    'is_complementary' => (bool) $group->is_complementary,
                    'value' => (string) $group->id,
                    'label' => $label,
                ];
            })
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
