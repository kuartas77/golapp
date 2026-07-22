<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InvoiceCustomItem;
use App\Models\School;
use Tests\TestCase;

final class AdminInvoiceCustomItemsTest extends TestCase
{
    public function testSchoolUserCanManageInvoiceCustomItemsThroughApi(): void
    {
        $this->actingAs($this->user);

        $otherSchool = School::factory()->create();
        InvoiceCustomItem::query()->create([
            'school_id' => $otherSchool->id,
            'type' => 'OTHER',
            'name' => 'Otro colegio',
            'unit_price' => 99999,
        ]);

        $createResponse = $this->postJson('/api/v2/admin/invoice-items-custom', [
            'item_type' => 'UNIFORM',
            'item_name' => 'Uniforme local',
            'item_unit_price' => '$ 85.000',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('message', 'Item personalizado creado correctamente.')
            ->assertJsonPath('data.type', 'UNIFORM')
            ->assertJsonPath('data.name', 'Uniforme local')
            ->assertJsonPath('data.unit_price', '85000.00');

        $itemId = $createResponse->json('data.id');

        $this->getJson('/api/v2/admin/invoice-items-custom')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $itemId);

        $this->getJson("/api/v2/admin/invoice-items-custom/{$itemId}")
            ->assertOk()
            ->assertJsonPath('id', $itemId)
            ->assertJsonPath('name', 'Uniforme local');

        $this->putJson("/api/v2/admin/invoice-items-custom/{$itemId}", [
            'item_type' => 'BALL',
            'item_name' => 'Balon entrenamiento',
            'item_unit_price' => '$ 120.000',
        ])->assertOk()
            ->assertJsonPath('message', 'Item personalizado actualizado correctamente.')
            ->assertJsonPath('data.type', 'BALL')
            ->assertJsonPath('data.name', 'Balon entrenamiento')
            ->assertJsonPath('data.unit_price', '120000.00');

        $this->deleteJson("/api/v2/admin/invoice-items-custom/{$itemId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('invoice_custom_items', [
            'id' => $itemId,
        ]);
    }
}
