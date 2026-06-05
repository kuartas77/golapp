<?php

namespace Database\Factories;

use App\Models\InventoryProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryProductFactory extends Factory
{
    protected $model = InventoryProduct::class;

    public function definition(): array
    {
        return [
            'school_id' => 1,
            'name' => $this->faker->words(2, true),
            'sku' => strtoupper($this->faker->bothify('SKU-####')),
            'category' => 'Uniformes',
            'description' => $this->faker->sentence(),
            'unit_price' => $this->faker->numberBetween(10000, 90000),
            'stock_quantity' => $this->faker->numberBetween(0, 30),
            'minimum_stock' => 5,
            'is_active' => true,
        ];
    }
}
