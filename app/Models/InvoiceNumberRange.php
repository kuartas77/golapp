<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceNumberRange extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'resolution_number', 'resolution_date', 'prefix', 'range_start', 'range_end',
        'next_number', 'valid_from', 'valid_until', 'technical_key', 'is_active', 'active_slot', 'used_at',
    ];

    protected $hidden = ['technical_key'];

    protected $casts = [
        'resolution_date' => 'date:Y-m-d',
        'range_start' => 'integer',
        'range_end' => 'integer',
        'next_number' => 'integer',
        'valid_from' => 'date:Y-m-d',
        'valid_until' => 'date:Y-m-d',
        'technical_key' => 'encrypted',
        'is_active' => 'boolean',
        'used_at' => 'datetime',
    ];

    protected $appends = ['has_technical_key', 'remaining_numbers', 'state'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'invoice_number_range_id');
    }

    public function getHasTechnicalKeyAttribute(): bool
    {
        return filled($this->getRawOriginal('technical_key'));
    }

    public function getRemainingNumbersAttribute(): int
    {
        return max(0, $this->range_end - $this->next_number + 1);
    }

    public function getStateAttribute(): string
    {
        if ($this->next_number > $this->range_end) {
            return 'exhausted';
        }
        if ($this->valid_until->isBefore(today())) {
            return 'expired';
        }
        if ($this->valid_from->isAfter(today())) {
            return 'future';
        }

        return $this->is_active ? 'active' : 'available';
    }
}
