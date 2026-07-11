<?php

declare(strict_types=1);

namespace App\Service\Evaluations;

use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlayerEvaluationQueryService
{
    public function paginate(int $schoolId, array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return PlayerEvaluation::query()
            ->where('school_id', $schoolId)
            ->when(isInstructor(), fn ($query) => $query->whereHas('inscription.trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor()))
            ->with($this->relations())
            ->when($filters['player_id'] ?? null, fn ($query, $id) => $query->whereHas('inscription', fn ($subQuery) => $subQuery->where('player_id', $id)))
            ->when($filters['training_group_id'] ?? null, fn ($query, $id) => $query->whereHas('inscription', fn ($subQuery) => $subQuery->where('training_group_id', $id)))
            ->when($filters['evaluation_period_id'] ?? null, fn ($query, $id) => $query->where('evaluation_period_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['evaluation_type'] ?? null, fn ($query, $type) => $query->where('evaluation_type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->whereHas('inscription.player', function ($player) use ($search) {
                        $player->where('names', 'like', "%{$search}%")
                            ->orWhere('last_names', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(COALESCE(names, ''), ' ', COALESCE(last_names, '')) LIKE ?", ["%{$search}%"])
                            ->orWhere('unique_code', 'like', "%{$search}%");
                    })->orWhereHas('inscription.trainingGroup', fn ($group) => $group->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(max(1, min(100, (int) ($filters['per_page'] ?? 15))));
    }

    public function scopedEvaluation(PlayerEvaluation $evaluation, int $schoolId): PlayerEvaluation
    {
        $query = PlayerEvaluation::query()->whereKey($evaluation->id)->where('school_id', $schoolId);
        if (isInstructor()) {
            $query->whereHas('inscription.trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor());
        }
        abort_unless($query->exists(), 404);
        return $evaluation;
    }

    public function scopedInscriptions(int $schoolId): Builder
    {
        return Inscription::query()->where('school_id', $schoolId)
            ->when(isInstructor(), fn ($query) => $query->whereHas('trainingGroup', fn ($groupQuery) => $groupQuery->byInstructor()));
    }

    public function relations(): array
    {
        return ['inscription.player', 'inscription.trainingGroup', 'period', 'template.trainingGroup', 'evaluator', 'scores.criterion'];
    }
}
