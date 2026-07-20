<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\TrainingGroup;

class PlayerEvaluationFormService
{
    public function __construct(private PlayerEvaluationQueryService $queries)
    {
    }

    public function options(int $schoolId, ?int $inscriptionId = null): array
    {
        return [
            'filters' => [
                'players' => $this->playerOptions($schoolId),
                'training_groups' => $this->trainingGroupOptions($schoolId),
                'periods' => $this->periodOptions($schoolId),
                'statuses' => EvaluationOptions::statuses(),
                'evaluation_types' => EvaluationOptions::evaluationTypes(),
            ],
            'selection' => [
                'inscriptions' => $this->inscriptionOptions($schoolId),
                'periods' => $this->periodOptions($schoolId),
                'templates' => $this->templateOptions($schoolId, $inscriptionId),
                'statuses' => EvaluationOptions::statuses(),
                'evaluation_types' => EvaluationOptions::evaluationTypes(),
            ],
            'scale_options' => config('evaluations.scale_options', []),
        ];
    }

    public function createContext(int $schoolId, int $inscriptionId, int $periodId, int $templateId): array
    {
        $inscription = $this->queries->scopedInscriptions($schoolId)
            ->with(['player', 'trainingGroup'])
            ->whereKey($inscriptionId)
            ->firstOrFail();

        $period = EvaluationPeriod::query()
            ->where('school_id', $schoolId)
            ->findOrFail($periodId);

        $template = EvaluationTemplate::query()
            ->with([
                'criteria' => fn ($q) => $q->orderBy('sort_order'),
                'trainingGroup',
            ])
            ->where('school_id', $schoolId)
            ->findOrFail($templateId);

        return $this->formContext($inscription, $period, $template, []);
    }

    public function editContext(PlayerEvaluation $evaluation): array
    {
        $evaluation->load([
            'inscription.player',
            'inscription.trainingGroup',
            'period',
            'template.criteria',
            'template.trainingGroup',
            'scores',
        ]);

        $template = $evaluation->template->load([
            'criteria' => fn ($q) => $q->orderBy('sort_order'),
            'trainingGroup',
        ]);

        $existingScores = $evaluation->scores
            ->keyBy('template_criterion_id')
            ->map(fn ($score) => [
                'template_criterion_id' => $score->template_criterion_id,
                'score' => $score->score,
                'scale_value' => $score->scale_value,
                'comment' => $score->comment,
            ])
            ->toArray();

        return $this->formContext($evaluation->inscription, $evaluation->period, $template, $existingScores);
    }

    public function normalizeScores(array $scores): array
    {
        return array_values($scores);
    }

    private function formContext(
        Inscription $inscription,
        EvaluationPeriod $period,
        EvaluationTemplate $template,
        array $existingScores
    ): array {
        return [
            'inscription' => $this->serializeInscription($inscription),
            'period' => $this->serializePeriod($period),
            'template' => $this->serializeTemplate($template),
            'criteria_by_dimension' => $this->criteriaByDimension($template),
            'existingScores' => $existingScores,
            'status_options' => EvaluationOptions::statuses(),
            'evaluation_type_options' => EvaluationOptions::evaluationTypes(),
            'scale_options' => config('evaluations.scale_options', []),
        ];
    }

    private function playerOptions(int $schoolId)
    {
        return Player::query()
            ->where('school_id', $schoolId)
            ->whereHas('inscription', function ($query) use ($schoolId): void {
                $query->where('school_id', $schoolId)
                    ->where('year', now()->year);

                applyInstructorTrainingGroupFilter($query);
            }, '=', 1)
            ->orderBy('names')
            ->orderBy('last_names')
            ->get()
            ->map(fn (Player $player) => [
                'id' => $player->id,
                'name' => $this->playerName($player),
                'unique_code' => $player->unique_code,
            ])
            ->values();
    }

    private function trainingGroupOptions(int $schoolId)
    {
        return TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->where('year_active', now()->year)
            ->when(isInstructor(), fn ($query) => $query->byInstructor())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (TrainingGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])
            ->values();
    }

    private function periodOptions(int $schoolId)
    {
        return EvaluationPeriod::query()
            ->where('school_id', $schoolId)
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (EvaluationPeriod $period) => $this->serializePeriod($period))
            ->values();
    }

    private function inscriptionOptions(int $schoolId)
    {
        return $this->queries->scopedInscriptions($schoolId)
            ->with(['player', 'trainingGroup'])
            ->where('year', now()->year)
            ->latest('id')
            ->get()
            ->map(function (Inscription $inscription): array {
                return [
                    'id' => $inscription->id,
                    'year' => $inscription->year,
                    'player_id' => $inscription->player_id,
                    'player_name' => $this->playerName($inscription->player),
                    'training_group_id' => $inscription->training_group_id,
                    'training_group_name' => $inscription->trainingGroup?->name,
                    'label' => sprintf(
                        '#%s - %s%s',
                        $inscription->id,
                        $this->playerName($inscription->player),
                        $inscription->trainingGroup?->name ? ' - ' . $inscription->trainingGroup->name : ''
                    ),
                ];
            })
            ->values();
    }

    private function templateOptions(int $schoolId, ?int $inscriptionId = null)
    {
        $trainingGroupId = null;

        if ($inscriptionId) {
            $trainingGroupId = $this->queries->scopedInscriptions($schoolId)
                ->whereKey($inscriptionId)
                ->value('training_group_id');

            if (!$trainingGroupId) {
                return collect();
            }
        }

        return EvaluationTemplate::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('trainingGroup')
            ->when($trainingGroupId, function ($query) use ($trainingGroupId): void {
                $query->where(function ($nestedQuery) use ($trainingGroupId): void {
                    $nestedQuery->whereNull('training_group_id')
                        ->orWhere('training_group_id', $trainingGroupId);
                });
            })
            ->orderByDesc('year')
            ->orderBy('name')
            ->get()
            ->map(fn (EvaluationTemplate $template) => $this->serializeTemplate($template))
            ->values();
    }

    private function criteriaByDimension(EvaluationTemplate $template): array
    {
        return $template->criteria
            ->groupBy(fn ($criterion) => $criterion->dimension ?: 'Sin dimensión')
            ->map(function ($criteria) {
                return $criteria->map(fn ($criterion) => [
                    'id' => $criterion->id,
                    'code' => $criterion->code,
                    'dimension' => $criterion->dimension,
                    'name' => $criterion->name,
                    'description' => $criterion->description,
                    'score_type' => $criterion->score_type,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                    'weight' => $criterion->weight,
                    'sort_order' => $criterion->sort_order,
                    'is_required' => (bool) $criterion->is_required,
                    'scale_options' => config('evaluations.scale_options.' . $criterion->score_type, []),
                ])->values();
            })
            ->toArray();
    }

    private function serializeInscription(Inscription $inscription): array
    {
        return [
            'id' => $inscription->id,
            'year' => $inscription->year,
            'player_id' => $inscription->player_id,
            'training_group_id' => $inscription->training_group_id,
            'player' => [
                'id' => $inscription->player?->id,
                'name' => $this->playerName($inscription->player),
                'unique_code' => $inscription->player?->unique_code,
                'photo_url' => $inscription->player?->photo_url,
            ],
            'training_group' => [
                'id' => $inscription->trainingGroup?->id,
                'name' => $inscription->trainingGroup?->name,
            ],
        ];
    }

    private function serializePeriod(EvaluationPeriod $period): array
    {
        return [
            'id' => $period->id,
            'name' => $period->name,
            'code' => $period->code,
            'year' => $period->year,
            'starts_at' => optional($period->starts_at)->format('Y-m-d'),
            'ends_at' => optional($period->ends_at)->format('Y-m-d'),
            'sort_order' => $period->sort_order,
            'is_active' => (bool) $period->is_active,
        ];
    }

    private function serializeTemplate(EvaluationTemplate $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'year' => $template->year,
            'status' => $template->status,
            'version' => $template->version,
            'training_group_id' => $template->training_group_id,
            'training_group_name' => $template->trainingGroup?->name,
        ];
    }

    private function playerName(?Player $player): string
    {
        if (!$player) {
            return 'Jugador sin nombre';
        }

        return $player->full_names
            ?? $player->full_name
            ?? $player->name
            ?? ('Jugador #' . $player->id);
    }
}
