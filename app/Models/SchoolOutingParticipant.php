<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolOutingParticipant extends Model
{
    use GeneralScopes;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_outing_id',
        'school_id',
        'inscription_id',
        'player_id',
        'target_amount',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
    ];

    protected $appends = [
        'raised_total',
        'pending_total',
        'progress_percent',
        'status_label',
    ];

    public function outing(): BelongsTo
    {
        return $this->belongsTo(SchoolOuting::class, 'school_outing_id');
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SchoolOutingContribution::class, 'school_outing_participant_id');
    }

    public function getRaisedTotalAttribute(): float
    {
        if (array_key_exists('contributions_sum_amount', $this->attributes)) {
            return (float) $this->attributes['contributions_sum_amount'];
        }

        if ($this->relationLoaded('contributions')) {
            return (float) $this->contributions->sum('amount');
        }

        return (float) $this->contributions()->sum('amount');
    }

    public function getPendingTotalAttribute(): float
    {
        return max(0, (float) $this->target_amount - $this->raised_total);
    }

    public function getProgressPercentAttribute(): int
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->raised_total / (float) $this->target_amount) * 100));
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->pending_total <= 0 ? 'Cumplido' : 'Pendiente';
    }
}
