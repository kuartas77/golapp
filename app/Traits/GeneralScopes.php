<?php

namespace App\Traits;

trait GeneralScopes
{
    public function scopeSchool($query)
    {
        return $query->when(isSchool() || isInstructor(), fn($query) => $query->where('school_id', auth()->user()->school->id));
    }

    public function scopeTrainingTeam($query, $training_team_id = null)
    {
        return $query->when($training_team_id, function($q) use($training_team_id){
            $q->where('training_team_id', $training_team_id);
        });
    }
}