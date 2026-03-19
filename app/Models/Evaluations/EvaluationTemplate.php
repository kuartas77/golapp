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
}
