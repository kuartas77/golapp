<?php

namespace App\Traits;

trait GeneralScopes
{
    public function scopeSchool($query, int $school_id = null)
    {
        return $query->when($school_id, function($q) use($school_id){
            $q->where('school_id', $school_id);
        });
    }
}