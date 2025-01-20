<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSession extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    protected $table = 'training_sessions';

    protected $fillable = [
        'school_id',
        'user_id',
        'training_group_id',
        'year',
        'period',
        'session',
        'date',
        'hour',
        'training_ground',
        'warm_up',
        'coaches',
        'material',
        'feedback',
        'incidents',
        'players',
        'absences',
        'back_to_calm',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function training_group(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function tasks()
    {
        return $this->hasMany(TrainingSessionDetail::class, 'training_session_id', 'id');
    }
}
