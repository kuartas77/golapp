<?php

namespace App\Service\DataTables;

use App\Models\Evaluations\PlayerEvaluation;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PlayerEvaluationDataTableService
{
    private const STATUS = ['draft' => 'Borrador', 'completed' => 'Completada', 'closed' => 'Cerrada'];
    private const TYPES = ['initial' => 'Inicial', 'periodic' => 'Periódica', 'final' => 'Final', 'special' => 'Especial'];

    public function __construct(private InstructorPeriodEditPolicy $periodPolicy) {}

    public function evaluations(int $schoolId, array $filters): JsonResponse
    {
        $query = PlayerEvaluation::query()->select('player_evaluations.*')
            ->join('inscriptions', 'inscriptions.id', '=', 'player_evaluations.inscription_id')
            ->leftJoin('players', 'players.id', '=', 'inscriptions.player_id')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'inscriptions.training_group_id')
            ->leftJoin('evaluation_periods', 'evaluation_periods.id', '=', 'player_evaluations.evaluation_period_id')
            ->leftJoin('evaluation_templates', 'evaluation_templates.id', '=', 'player_evaluations.evaluation_template_id')
            ->where('player_evaluations.school_id', $schoolId)
            ->when(isInstructor(), fn ($query) => $query->whereHas('inscription.trainingGroup', fn ($group) => $group->byInstructor()))
            ->with(['inscription.player', 'inscription.trainingGroup', 'period', 'template.trainingGroup']);

        return datatables()->eloquent($query)
            ->filter(function ($query) use ($filters) {
                $query->when($filters['player_id'] ?? null, fn ($q, $value) => $q->where('inscriptions.player_id', $value))
                    ->when($filters['training_group_id'] ?? null, fn ($q, $value) => $q->where('inscriptions.training_group_id', $value))
                    ->when($filters['evaluation_period_id'] ?? null, fn ($q, $value) => $q->where('player_evaluations.evaluation_period_id', $value))
                    ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('player_evaluations.status', $value))
                    ->when($filters['evaluation_type'] ?? null, fn ($q, $value) => $q->where('player_evaluations.evaluation_type', $value));
                $keyword = trim((string) data_get($filters, 'search.value', ''));
                if ($keyword === '') return;
                $like = "%{$keyword}%";
                $status = $this->matchingKeys(self::STATUS, $keyword);
                $types = $this->matchingKeys(self::TYPES, $keyword);
                $query->where(function ($search) use ($like, $status, $types) {
                    $search->where('player_evaluations.id', 'like', $like)->orWhere('player_evaluations.status', 'like', $like)
                        ->orWhere('player_evaluations.evaluation_type', 'like', $like)->orWhere('player_evaluations.overall_score', 'like', $like)
                        ->orWhere('players.unique_code', 'like', $like)->orWhere('players.names', 'like', $like)->orWhere('players.last_names', 'like', $like)
                        ->orWhereRaw("CONCAT(COALESCE(players.names, ''), ' ', COALESCE(players.last_names, '')) LIKE ?", [$like])
                        ->orWhere('training_groups.name', 'like', $like)->orWhere('evaluation_periods.name', 'like', $like)->orWhere('evaluation_templates.name', 'like', $like);
                    if ($status) $search->orWhereIn('player_evaluations.status', $status);
                    if ($types) $search->orWhereIn('player_evaluations.evaluation_type', $types);
                });
            })
            ->orderColumn('player_name', fn ($query, $order) => $query->orderBy('players.last_names', $order)->orderBy('players.names', $order))
            ->orderColumn('player_code', 'players.unique_code $1')->orderColumn('training_group_name', 'training_groups.name $1')
            ->orderColumn('period_name', 'evaluation_periods.name $1')->orderColumn('template_name', 'evaluation_templates.name $1')
            ->orderColumn('evaluation_type_label', 'player_evaluations.evaluation_type $1')->orderColumn('status_label', 'player_evaluations.status $1')
            ->editColumn('evaluated_at', fn (PlayerEvaluation $evaluation) => $evaluation->evaluated_at?->toISOString())
            ->addColumn('player_id', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->player_id)
            ->addColumn('player_name', fn (PlayerEvaluation $evaluation) => $this->playerName($evaluation))
            ->addColumn('player_code', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->player?->unique_code ?? '')
            ->addColumn('player_photo_url', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->player?->photo_url ?? '/img/user.webp')
            ->addColumn('training_group_id', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->training_group_id)
            ->addColumn('training_group_name', fn (PlayerEvaluation $evaluation) => $evaluation->inscription?->trainingGroup?->name ?? '')
            ->addColumn('period_name', fn (PlayerEvaluation $evaluation) => $evaluation->period?->name ?? '')
            ->addColumn('template_name', fn (PlayerEvaluation $evaluation) => $evaluation->template?->name ?? '')
            ->addColumn('evaluation_type_label', fn (PlayerEvaluation $evaluation) => self::TYPES[$evaluation->evaluation_type] ?? $evaluation->evaluation_type)
            ->addColumn('status_label', fn (PlayerEvaluation $evaluation) => self::STATUS[$evaluation->status] ?? $evaluation->status)
            ->addColumn('is_closed', fn (PlayerEvaluation $evaluation) => (bool) $evaluation->is_closed)
            ->addColumn('period_locked', fn (PlayerEvaluation $evaluation) => !$this->periodPolicy->canMutateDate($evaluation->evaluated_at ?? now()))
            ->addColumn('urls', fn (PlayerEvaluation $evaluation) => ['show' => url("/player-evaluations/{$evaluation->id}"), 'edit' => url("/player-evaluations/{$evaluation->id}/edit"), 'pdf' => route('player-evaluations.pdf', $evaluation->id)])
            ->toJson();
    }

    private function playerName(PlayerEvaluation $evaluation): string
    {
        $player = $evaluation->inscription?->player;
        return $player ? ($player->full_names ?? $player->full_name ?? $player->name ?? 'Jugador #'.$player->id) : '';
    }

    private function matchingKeys(array $labels, string $keyword): array
    {
        $keyword = Str::of($keyword)->ascii()->lower()->toString();
        return collect($labels)->filter(fn (string $label, string $value) => Str::of($label)->ascii()->lower()->contains($keyword) || Str::of($value)->ascii()->lower()->contains($keyword))->keys()->values()->all();
    }
}
