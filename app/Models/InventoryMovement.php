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
        'price_snapshot',
        'stock_before',
        'stock_after',
        'reason',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_snapshot' => 'decimal:2',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'movement_date' => 'date:Y-m-d',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
