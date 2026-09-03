<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingSessionDetail extends Model
{
    use GeneralScopes;
    use HasFactory;
    use SoftDeletes;

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
        'observations',
    ];

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
