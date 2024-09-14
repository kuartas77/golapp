<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait GeneralScopes
{
    public function scopeSchoolId(Builder $query): void
    {
        $query->where($this->table.'.school_id', getSchool(auth()->user())->id);
    }

    public function scopeTrainingTeam(Builder $query, $training_team_id = null): void
    {
        $query->when($training_team_id, function ($q) use ($training_team_id) {
            $q->where('training_team_id', $training_team_id);
        });
    }

    public function scopeWhenLastMonthYear(Builder $query): void
    {
        $now = now();
        $query->when(
            ($now->month <> 12),
            fn ($query) => $query->where('year', $now->year),
            fn ($query) => $query->where(fn ($q) => $q->where('year', $now->year)->orWhere('year', $now->addYear()->year))
        );
    }

    public function scopeInscriptionYear(Builder $query, int|null $year): void
    {
        $query->where('year', $year ? $year : now()->year);
    }
}
