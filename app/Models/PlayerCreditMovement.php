<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCreditMovement extends Model
{
    use GeneralScopes;
    use HasFactory;

    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';

    protected $fillable = [
        'school_id',
        'player_id',
        'type',
        'amount',
        'movement_date',
        'concept',
        'notes',
        'payment_id',
        'payment_field',
        'previous_payment_status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'movement_date' => 'date:Y-m-d',
        'previous_payment_status' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
