<?php

namespace App\Models\Evaluations;

use App\Models\Evaluations\PlayerEvaluationScore;
use App\Models\Inscription;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscription_id',
        'evaluation_period_id',
        'evaluation_template_id',
        'evaluator_user_id',
        'evaluation_type',
        'status',
        'evaluated_at',
        'general_comment',
        'strengths',
        'improvement_opportunities',
        'recommendations',
        'overall_score',
        'school_id'
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
        'overall_score' => 'float',
    ];

    protected $appends = ['is_closed', 'is_completed'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluation_period_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(PlayerEvaluationScore::class)
            ->with('criterion');
    }

    public function completedScores(): HasMany
    {
        return $this->hasMany(PlayerEvaluationScore::class)
            ->whereNotNull('score')
            ->with('criterion');
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === 'closed';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || $this->status === 'closed';
    }
}
