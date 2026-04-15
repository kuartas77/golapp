<?php

namespace App\Models\Evaluations;

use App\Models\Evaluations\EvaluationTemplateCriterion;
use App\Models\Evaluations\PlayerEvaluation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerEvaluationScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_evaluation_id',
        'template_criterion_id',
        'score',
        'scale_value',
        'comment',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(PlayerEvaluation::class, 'player_evaluation_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplateCriterion::class, 'template_criterion_id');
    }
}
