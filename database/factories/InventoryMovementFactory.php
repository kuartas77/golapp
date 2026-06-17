<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        $stockBefore = $this->faker->numberBetween(0, 20);
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'school_id' => 1,
            'inventory_product_id' => 1,
            'user_id' => 1,
            'type' => InventoryMovement::TYPE_ENTRY,
            'quantity' => $quantity,
            'entry_price_snapshot' => 30000,
            'sale_price_snapshot' => 50000,
            'price_snapshot' => $this->faker->numberBetween(10000, 90000),
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore + $quantity,
            'reason' => 'Compra',
            'notes' => null,
            'movement_date' => now()->toDateString(),
        ];
    }
}
