<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\EvaluationTemplateCriterion;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Evaluations\PlayerEvaluationScore;
use App\Models\Inscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlayerEvaluationCrudService
{
    public function __construct(
        private PlayerEvaluationScoreCalculator $calculator
    ) {}

    public function create(Inscription $inscription, array $data, int $userId): PlayerEvaluation
    {
        $this->ensureUniquePeriod(
            inscriptionId: $inscription->id,
            evaluationPeriodId: (int) $data['evaluation_period_id']
        );

        $this->ensureTemplateBelongsToInscription(
            inscription: $inscription,
            templateId: (int) $data['evaluation_template_id']
        );

        return DB::transaction(function () use ($inscription, $data, $userId) {
            $evaluation = PlayerEvaluation::create([
                'inscription_id' => $inscription->id,
                'evaluation_period_id' => $data['evaluation_period_id'],
                'evaluation_template_id' => $data['evaluation_template_id'],
                'evaluator_user_id' => $userId,
                'evaluation_type' => $data['evaluation_type'] ?? 'periodic',
                'status' => $data['status'] ?? 'draft',
                'evaluated_at' => $data['evaluated_at'] ?? null,
                'general_comment' => $data['general_comment'] ?? null,
                'strengths' => $data['strengths'] ?? null,
                'improvement_opportunities' => $data['improvement_opportunities'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
            ]);

            if (!empty($data['scores'])) {
                $this->syncScores($evaluation, $data['scores'], false);
            }

            return $this->finalize($evaluation);
        });
    }

    public function update(PlayerEvaluation $evaluation, array $data): PlayerEvaluation
    {
        if ($evaluation->status === 'closed') {
            throw ValidationException::withMessages([
                'status' => ['No se puede editar una evaluación cerrada.'],
            ]);
        }

        $nextPeriodId = (int) ($data['evaluation_period_id'] ?? $evaluation->evaluation_period_id);
        $nextTemplateId = (int) ($data['evaluation_template_id'] ?? $evaluation->evaluation_template_id);

        $this->ensureUniquePeriod(
            inscriptionId: $evaluation->inscription_id,
            evaluationPeriodId: $nextPeriodId,
            ignoreEvaluationId: $evaluation->id
        );

        $this->ensureTemplateBelongsToInscription(
            inscription: $evaluation->inscription,
            templateId: $nextTemplateId
        );

        return DB::transaction(function () use ($evaluation, $data, $nextPeriodId, $nextTemplateId) {
            $evaluation->fill([
                'evaluation_period_id' => $nextPeriodId,
                'evaluation_template_id' => $nextTemplateId,
                'evaluation_type' => $data['evaluation_type'] ?? $evaluation->evaluation_type,
                'status' => $data['status'] ?? $evaluation->status,
                'evaluated_at' => $data['evaluated_at'] ?? $evaluation->evaluated_at,
                'general_comment' => $data['general_comment'] ?? $evaluation->general_comment,
                'strengths' => $data['strengths'] ?? $evaluation->strengths,
                'improvement_opportunities' => $data['improvement_opportunities'] ?? $evaluation->improvement_opportunities,
                'recommendations' => $data['recommendations'] ?? $evaluation->recommendations,
            ]);

            if (
                in_array($evaluation->status, ['completed', 'closed'], true) &&
                empty($evaluation->evaluated_at)
            ) {
                $evaluation->evaluated_at = now();
            }

            $evaluation->save();

            if (array_key_exists('scores', $data)) {
                $this->syncScores($evaluation, $data['scores'] ?? [], true);
            }

            return $this->finalize($evaluation);
        });
    }

    public function delete(PlayerEvaluation $evaluation): void
    {
        if ($evaluation->status === 'closed') {
            throw ValidationException::withMessages([
                'status' => ['No se puede eliminar una evaluación cerrada.'],
            ]);
        }

        $evaluation->delete();
    }

    private function finalize(PlayerEvaluation $evaluation): PlayerEvaluation
    {
        $evaluation->loadMissing([
            'inscription',
            'scores.criterion',
        ]);

        $scoreErrors = $this->calculator->validateScores($evaluation);

        if (!empty($scoreErrors)) {
            throw ValidationException::withMessages([
                'scores' => $scoreErrors,
            ]);
        }

        $this->ensureRequiredCriteriaIfCompleted($evaluation);

        $this->calculator->recalculateAndSave($evaluation);

        return $evaluation->refresh();
    }

    private function syncScores(PlayerEvaluation $evaluation, array $scores, bool $replace): void
    {
        $criteria = EvaluationTemplateCriterion::query()
            ->where('evaluation_template_id', $evaluation->evaluation_template_id)
            ->get()
            ->keyBy('id');

        $payloadCriterionIds = [];

        foreach ($scores as $scoreData) {
            $criterionId = (int) $scoreData['template_criterion_id'];
            $criterion = $criteria->get($criterionId);

            if (!$criterion) {
                throw ValidationException::withMessages([
                    'scores' => [
                        "El criterio {$criterionId} no pertenece a la plantilla seleccionada.",
                    ],
                ]);
            }

            $payloadCriterionIds[] = $criterionId;

            PlayerEvaluationScore::updateOrCreate(
                [
                    'player_evaluation_id' => $evaluation->id,
                    'template_criterion_id' => $criterionId,
                ],
                [
                    'score' => $scoreData['score'] ?? null,
                    'scale_value' => $scoreData['scale_value'] ?? null,
                    'comment' => $scoreData['comment'] ?? null,
                ]
            );
        }

        if ($replace) {
            $query = PlayerEvaluationScore::query()
                ->where('player_evaluation_id', $evaluation->id);

            if (!empty($payloadCriterionIds)) {
                $query->whereNotIn('template_criterion_id', $payloadCriterionIds);
            }

            $query->delete();
        }
    }

    private function ensureUniquePeriod(
        int $inscriptionId,
        int $evaluationPeriodId,
        ?int $ignoreEvaluationId = null
    ): void {
        $query = PlayerEvaluation::query()
            ->where('inscription_id', $inscriptionId)
            ->where('evaluation_period_id', $evaluationPeriodId);

        if ($ignoreEvaluationId) {
            $query->where('id', '!=', $ignoreEvaluationId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'evaluation_period_id' => [
                    'Ya existe una evaluación para esta inscripción en ese período.',
                ],
            ]);
        }
    }

    private function ensureTemplateBelongsToInscription(Inscription $inscription, int $templateId): void
    {
        $template = EvaluationTemplate::findOrFail($templateId);

        if (
            !empty($template->training_group_id) &&
            !empty($inscription->training_group_id) &&
            (int) $template->training_group_id !== (int) $inscription->training_group_id
        ) {
            throw ValidationException::withMessages([
                'evaluation_template_id' => [
                    'La plantilla no corresponde al grupo de entrenamiento de la inscripción.',
                ],
            ]);
        }
    }

    private function ensureRequiredCriteriaIfCompleted(PlayerEvaluation $evaluation): void
    {
        if (!in_array($evaluation->status, ['completed', 'closed'], true)) {
            return;
        }

        $requiredCriterionIds = EvaluationTemplateCriterion::query()
            ->where('evaluation_template_id', $evaluation->evaluation_template_id)
            ->where('is_required', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $completedCriterionIds = $evaluation->scores
            ->filter(fn ($score) => $score->score !== null || $score->scale_value !== null)
            ->pluck('template_criterion_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missing = array_diff($requiredCriterionIds, $completedCriterionIds);

        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'scores' => [
                    'Faltan criterios obligatorios por diligenciar para completar/cerrar la evaluación.',
                ],
            ]);
        }
    }
}
