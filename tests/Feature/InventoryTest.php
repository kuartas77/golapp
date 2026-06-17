<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use Tests\TestCase;

final class InventoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_inventory_permission_and_role_block_instructors(): void
    {
        $school = School::findOrFail($this->school['id']);
        $this->setInventoryPermission($school, true);

        $instructor = $this->createUser([
            'email' => sprintf('inventory-instructor-%s@example.com', uniqid()),
            'school_id' => $school->id,
        ], ['instructor']);
        SchoolUser::query()->create([
            'school_id' => $school->id,
            'user_id' => $instructor->id,
        ]);

        $this->actingAs($instructor)
            ->getJson('/api/v2/inventory/products')
            ->assertForbidden();

        $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/inventory_products?draw=1&start=0&length=10')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->getJson('/api/v2/inventory/products')
            ->assertOk();

        $this->setInventoryPermission($school, false);

        $this->actingAs($this->user)
            ->getJson('/api/v2/inventory/products')
            ->assertForbidden();
    }

    public function test_inventory_products_are_scoped_by_school(): void
    {
        $otherSchool = School::factory()->create([
            'email' => 'inventory-other-school@example.com',
            'slug' => 'inventory-other-school',
        ]);

        $ownProduct = InventoryProduct::factory()->create([
            'school_id' => $this->school['id'],
            'name' => 'Balón Golapp',
        ]);
        $otherProduct = InventoryProduct::factory()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Producto externo',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/inventory/products')
            ->assertOk();

        $this->assertTrue(collect($response->json('data'))->contains('id', $ownProduct->id));
        $this->assertFalse(collect($response->json('data'))->contains('id', $otherProduct->id));

        $this->actingAs($this->user)
            ->getJson("/api/v2/inventory/products/{$ownProduct->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ownProduct->id);

        $this->actingAs($this->user)
            ->getJson("/api/v2/inventory/products/{$otherProduct->id}")
            ->assertNotFound();
    }

    public function test_product_crud_and_initial_stock_creates_auditable_movement(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v2/inventory/products', [
                'name' => 'Camiseta local',
                'sku' => 'CAM-001',
                'category' => 'Uniformes',
                'description' => 'Camiseta principal',
                'entry_price' => 35000,
                'unit_price' => 75000,
                'stock_quantity' => 10,
                'minimum_stock' => 3,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.entry_price', '35000.00')
            ->assertJsonPath('data.stock_quantity', 10);

        $productId = $response->json('data.id');

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_product_id' => $productId,
            'type' => InventoryMovement::TYPE_ADJUSTMENT,
            'quantity' => 10,
            'entry_price_snapshot' => '35000.00',
            'sale_price_snapshot' => '75000.00',
            'stock_before' => 0,
            'stock_after' => 10,
            'reason' => 'Stock inicial',
        ]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/inventory/products/{$productId}", [
                'name' => 'Camiseta visitante',
                'sku' => 'CAM-002',
                'category' => 'Uniformes',
                'description' => 'Camiseta actualizada',
                'entry_price' => 41000,
                'unit_price' => 82000,
                'stock_quantity' => 999,
                'minimum_stock' => 4,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Camiseta visitante')
            ->assertJsonPath('data.entry_price', '41000.00')
            ->assertJsonPath('data.stock_quantity', 10);
    }

    public function test_movements_update_stock_and_block_negative_stock(): void
    {
        $product = InventoryProduct::factory()->create([
            'school_id' => $this->school['id'],
            'entry_price' => 30000,
            'unit_price' => 50000,
            'stock_quantity' => 5,
            'minimum_stock' => 2,
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/v2/inventory/products/{$product->id}/movements", [
                'type' => InventoryMovement::TYPE_ENTRY,
                'quantity' => 3,
                'reason' => 'Compra',
                'movement_date' => '2026-06-05',
            ])
            ->assertCreated()
            ->assertJsonPath('data.stock_before', 5)
            ->assertJsonPath('data.stock_after', 8)
            ->assertJsonPath('data.entry_price_snapshot', '30000.00')
            ->assertJsonPath('data.sale_price_snapshot', '50000.00')
            ->assertJsonPath('data.profit_margin', 0);

        $this->assertSame(8, $product->fresh()->stock_quantity);

        $this->actingAs($this->user)
            ->postJson("/api/v2/inventory/products/{$product->id}/movements", [
                'type' => InventoryMovement::TYPE_EXIT,
                'quantity' => 6,
                'reason' => 'Entrega',
                'movement_date' => '2026-06-05',
            ])
            ->assertCreated()
            ->assertJsonPath('data.stock_before', 8)
            ->assertJsonPath('data.stock_after', 2)
            ->assertJsonPath('data.entry_price_snapshot', '30000.00')
            ->assertJsonPath('data.sale_price_snapshot', '50000.00')
            ->assertJsonPath('data.profit_margin', 120000);

        $this->actingAs($this->user)
            ->postJson("/api/v2/inventory/products/{$product->id}/movements", [
                'type' => InventoryMovement::TYPE_EXIT,
                'quantity' => 3,
                'reason' => 'Salida inválida',
                'movement_date' => '2026-06-05',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_inventory_datatables_only_return_school_records(): void
    {
        $otherSchool = School::factory()->create([
            'email' => 'inventory-datatables-other@example.com',
            'slug' => 'inventory-datatables-other',
        ]);
        $ownProduct = InventoryProduct::factory()->create([
            'school_id' => $this->school['id'],
            'name' => 'Guayos',
            'stock_quantity' => 4,
            'minimum_stock' => 5,
        ]);
        InventoryProduct::factory()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Producto oculto',
        ]);
        InventoryMovement::factory()->create([
            'school_id' => $this->school['id'],
            'inventory_product_id' => $ownProduct->id,
            'user_id' => $this->user->id,
            'type' => InventoryMovement::TYPE_EXIT,
            'quantity' => 4,
            'entry_price_snapshot' => 30000,
            'sale_price_snapshot' => 50000,
            'stock_before' => 0,
            'stock_after' => 4,
        ]);

        $productsResponse = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/inventory_products?draw=1&start=0&length=10')
            ->assertOk();

        $this->assertTrue(collect($productsResponse->json('data'))->contains('id', $ownProduct->id));
        $this->assertTrue(collect($productsResponse->json('data'))->firstWhere('id', $ownProduct->id)['is_low_stock']);

        $movementsResponse = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/inventory_movements?draw=1&start=0&length=10')
            ->assertOk();

        $this->assertSame('Guayos', $movementsResponse->json('data.0.product_name'));
        $this->assertSame(80000, $movementsResponse->json('data.0.profit_margin'));
    }

    private function setInventoryPermission(School $school, bool $enabled): void
    {
        $permissions = array_merge($school->getResolvedSchoolPermissions(), [
            'school.module.inventory' => $enabled,
        ]);

        $school->forceFill([
            'school_permissions' => School::normalizeSchoolPermissions($permissions),
        ])->save();

        School::forgetCachedSchool($school->id);
    }
}
