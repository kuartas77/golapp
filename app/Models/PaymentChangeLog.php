<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentChangeLog extends Model
{
    protected $fillable = [
        'school_id',
        'payment_id',
        'inscription_id',
        'changed_by',
        'year',
        'field',
        'old_status',
        'new_status',
        'old_amount',
        'new_amount',
        'source',
        'reason',
    ];

    protected $casts = [
        'year' => 'integer',
        'old_status' => 'integer',
        'new_status' => 'integer',
        'old_amount' => 'integer',
        'new_amount' => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
