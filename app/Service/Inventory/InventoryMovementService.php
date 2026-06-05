<?php

declare(strict_types=1);

namespace App\Service\Inventory;

use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementService
{
    public function record(InventoryProduct $product, array $data): InventoryMovement
    {
        $schoolId = (int) getSchool(auth()->user())->id;

        return DB::transaction(function () use ($product, $data, $schoolId): InventoryMovement {
            $lockedProduct = InventoryProduct::query()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($product->id);

            $stockBefore = (int) $lockedProduct->stock_quantity;
            $quantity = (int) $data['quantity'];
            $stockAfter = $this->resolveStockAfter($data['type'], $stockBefore, $quantity);

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => ['La salida no puede superar el stock disponible.'],
                ]);
            }

            $lockedProduct->forceFill([
                'stock_quantity' => $stockAfter,
            ])->save();

            return InventoryMovement::query()->create([
                'school_id' => $schoolId,
                'inventory_product_id' => $lockedProduct->id,
                'user_id' => auth()->id(),
                'type' => $data['type'],
                'quantity' => $quantity,
                'price_snapshot' => $data['price_snapshot'] ?? $lockedProduct->unit_price,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'movement_date' => $data['movement_date'],
            ]);
        });
    }

    private function resolveStockAfter(string $type, int $stockBefore, int $quantity): int
    {
        return match ($type) {
            InventoryMovement::TYPE_ENTRY => $stockBefore + $quantity,
            InventoryMovement::TYPE_EXIT => $stockBefore - $quantity,
            InventoryMovement::TYPE_ADJUSTMENT => $quantity,
            default => $stockBefore,
        };
    }
}
