<?php

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscriptionCustomCharge extends Model
{
    use GeneralScopes;

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
        'due_date' => 'date',
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

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_DUE => 'Debe',
            self::STATUS_PAID => 'Pagado',
        ];
    }
}
