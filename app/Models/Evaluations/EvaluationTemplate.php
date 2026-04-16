<?php

namespace App\Models\Evaluations;

use App\Models\Evaluations\EvaluationTemplateCriterion;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class EvaluationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'year',
        'training_group_id',
        'status',
        'version',
        'created_by',
        'school_id'
    ];

    protected $casts = [
        'year' => 'integer',
        'version' => 'integer',
    ];

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationTemplateCriterion::class)
            ->orderBy('sort_order');
    }

    public function playerEvaluations(): HasMany
    {
        return $this->hasMany(PlayerEvaluation::class);
    }

    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    public function isInUse(): bool
    {
        if (isset($this->player_evaluations_count)) {
            return (int) $this->player_evaluations_count > 0;
        }

        return $this->playerEvaluations()->exists();
    }
}
