<?php

namespace App\Models\Evaluations;

use App\Models\Evaluations\PlayerEvaluation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'year',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
        'school_id'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
        'year' => 'integer',
        'sort_order' => 'integer',
    ];

    public function playerEvaluations(): HasMany
    {
        return $this->hasMany(PlayerEvaluation::class);
    }
}