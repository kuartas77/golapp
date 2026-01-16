<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniformRequest extends Model
{
    use HasFactory;
    use GeneralScopes;

    protected $table = "uniform_request";

    protected $fillable = [
        'school_id',
        'player_id',
        'type',
        'status',
        'quantity',
        'size',
        'additional_notes',
        'rejection_reason',
        'approved_at',
        'delivered_at',
        'rejected_at',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
