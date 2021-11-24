<?php

namespace App\Traits;

trait GeneralScopes
{
    public function scopeSchool($query)
    {
        return $query->when(isSchool(), fn($query) => $query->where('school_id', auth()->user()->school->id));
    }
}