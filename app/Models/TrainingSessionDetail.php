<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingSessionDetail extends Model
{
    use SoftDeletes;
    use GeneralScopes;
    use HasFactory;

    protected $table = 'training_session_details';
    protected $fillable = [
        'training_session_id',
        'task_number',
        'task_name',
        'general_objective',
        'specific_goal',
        'content_one',
        'content_two',
        'content_three',
        'ts',
        'sr',
        'tt',
        'observations'
    ];

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
