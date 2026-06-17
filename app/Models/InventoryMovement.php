<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use GeneralScopes;
    use HasFactory;

    public const TYPE_ENTRY = 'entry';

    public const TYPE_EXIT = 'exit';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'school_id',
        'inventory_product_id',
        'user_id',
        'type',
        'quantity',
        'entry_price_snapshot',
        'sale_price_snapshot',
        'price_snapshot',
        'stock_before',
        'stock_after',
        'reason',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'entry_price_snapshot' => 'decimal:2',
        'sale_price_snapshot' => 'decimal:2',
        'price_snapshot' => 'decimal:2',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'movement_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'profit_margin',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->type !== self::TYPE_EXIT) {
            return 0.0;
        }

        return ((float) $this->sale_price_snapshot - (float) $this->entry_price_snapshot) * (int) $this->quantity;
    }
}
