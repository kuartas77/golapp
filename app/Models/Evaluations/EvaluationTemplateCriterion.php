<?php

namespace App\Models\Evaluations;

use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\PlayerEvaluationScore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationTemplateCriterion extends Model
{
    use HasFactory;

    protected $table = 'evaluation_template_criteria';

    protected $fillable = [
        'evaluation_template_id',
        'code',
        'dimension',
        'name',
        'description',
        'score_type',
        'min_score',
        'max_score',
        'weight',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'min_score' => 'float',
        'max_score' => 'float',
        'weight' => 'float',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(PlayerEvaluationScore::class, 'template_criterion_id');
    }
}