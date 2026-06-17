<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolOuting extends Model
{
    use GeneralScopes;
    use HasFactory;
    use SoftDeletes;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'school_id',
        'name',
        'departure_date',
        'amount_per_player',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'departure_date' => 'date:Y-m-d',
        'amount_per_player' => 'decimal:2',
    ];

    protected $appends = [
        'participants_count_value',
        'target_total',
        'raised_total',
        'pending_total',
        'progress_percent',
        'status_label',
        'is_locked',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SchoolOutingParticipant::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(SchoolOutingActivity::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SchoolOutingContribution::class);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_CANCELLED], true);
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->isLocked();
    }

    public function getParticipantsCountValueAttribute(): int
    {
        if (array_key_exists('participants_count', $this->attributes)) {
            return (int) $this->attributes['participants_count'];
        }

        if ($this->relationLoaded('participants')) {
            return $this->participants->count();
        }

        return (int) $this->participants()->count();
    }

    public function getTargetTotalAttribute(): float
    {
        if (array_key_exists('participants_sum_target_amount', $this->attributes)) {
            return (float) $this->attributes['participants_sum_target_amount'];
        }

        if ($this->relationLoaded('participants')) {
            return (float) $this->participants->sum('target_amount');
        }

        return (float) $this->participants()->sum('target_amount');
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
        return max(0, $this->target_total - $this->raised_total);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->target_total <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->raised_total / $this->target_total) * 100));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CLOSED => 'Cerrada',
            self::STATUS_CANCELLED => 'Cancelada',
            default => 'Abierta',
        };
    }
}
