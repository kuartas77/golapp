<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscriptionCustomCharge extends Model
{
    use HasFactory;
    use GeneralScopes;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DUE = 'due';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'school_id',
        'inscription_id',
        'player_id',
        'invoice_custom_item_id',
        'invoice_item_id',
        'name',
        'value',
        'status',
        'due_date',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'due_date' => 'date:Y-m-d',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function invoiceCustomItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceCustomItem::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}
