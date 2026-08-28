<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSessionPhase extends Model
{
    protected $fillable = ['position', 'name', 'time', 'dosage', 'description', 'diagram', 'visual_mode', 'image_path'];

    protected $casts = ['diagram' => 'array'];

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
