<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\EvaluationTemplate;
use App\Models\TrainingGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationTemplateReadService
{
    public function options(int $schoolId): array
    {
        return [
            'filters' => [
                'years' => $this->yearOptions($schoolId),
                'statuses' => EvaluationOptions::templateStatuses(),
                'training_groups' => $this->trainingGroupOptions($schoolId),
            ],
            'editor' => [
                'statuses' => EvaluationOptions::templateStatuses(),
                'status_actions' => EvaluationOptions::templateStatusActions(),
                'score_types' => EvaluationOptions::scoreTypes(),
                'training_groups' => $this->trainingGroupOptions($schoolId),
                'scale_options' => config('evaluations.scale_options.scale', []),
            ],
        ];
    }

    public function datatable(Request $request, int $schoolId): JsonResponse
    {
        $search = trim((string) ($request->input('template_search') ?? data_get($request->input('search'), 'value', '')));

        $query = EvaluationTemplate::query()
            ->select('evaluation_templates.*')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'evaluation_templates.training_group_id')
            ->where('evaluation_templates.school_id', $schoolId)
            ->with(['trainingGroup'])
            ->withCount(['criteria', 'playerEvaluations']);

        return datatables()->eloquent($query)
            ->filter(function ($query) use ($request, $search): void {
                $query->when($request->filled('year'), fn ($q) => $q->where('evaluation_templates.year', (int) $request->input('year')))
                    ->when($request->filled('status'), fn ($q) => $q->where('evaluation_templates.status', $request->input('status')))
                    ->when($request->filled('training_group_id'), fn ($q) => $q->where('evaluation_templates.training_group_id', $request->integer('training_group_id')));

                if ($search === '') {
                    return;
                }

                $like = "%{$search}%";
                $query->where(function ($nestedQuery) use ($like): void {
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

    private function trainingGroupOptions(int $schoolId): array
    {
        return TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name', 'is_complementary'])
            ->map(function (TrainingGroup $group): array {
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
