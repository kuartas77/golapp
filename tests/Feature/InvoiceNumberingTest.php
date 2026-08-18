<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\InvoiceNumberRange;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolInvoiceSequence;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class InvoiceNumberingTest extends TestCase
{
    public function test_numbering_configuration_page_depends_on_electronic_mode_for_school_users(): void
    {
        $this->actingAs($this->user)
            ->get('/configuracion/numeracion-facturacion')
            ->assertForbidden();
        $this->actingAs($this->user)
            ->get('/administracion/numeracion-facturacion')
            ->assertForbidden();

        getSchool($this->user)->forceFill(['electronic_invoicing_enabled' => true])->save();

        $this->actingAs($this->user)
            ->get('/configuracion/numeracion-facturacion')
            ->assertOk();

        getSchool($this->user)->forceFill(['electronic_invoicing_enabled' => false])->save();
        $superAdmin = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => 'numbering-page-superadmin@example.test',
        ], [User::SUPER_ADMIN]);

        $this->actingAs($superAdmin)
            ->get('/configuracion/numeracion-facturacion')
            ->assertOk();
    }

    public function test_internal_invoices_continue_the_school_seed_with_six_digits(): void
    {
        $this->actingAs($this->user);
        SchoolInvoiceSequence::query()
            ->where('school_id', $this->school['id'])
            ->update(['next_number' => 1001]);

        $first = $this->storeInvoice('internal-sequence-1');
        $second = $this->storeInvoice('internal-sequence-2');

        $this->assertSame('FAC-001001', Invoice::query()->findOrFail($first['id'])->invoice_number);
        $this->assertSame('FAC-001002', Invoice::query()->findOrFail($second['id'])->invoice_number);
        $this->assertDatabaseHas('school_invoice_sequences', [
            'school_id' => $this->school['id'],
            'next_number' => 1003,
        ]);
    }

    public function test_electronic_invoices_consume_the_active_authorized_range_exactly(): void
    {
        $this->actingAs($this->user);
        getSchool($this->user)->forceFill(['electronic_invoicing_enabled' => true])->save();
        $range = InvoiceNumberRange::query()->create([
            'school_id' => $this->school['id'],
            'resolution_number' => '18764099999999',
            'resolution_date' => now()->subDay()->toDateString(),
            'prefix' => 'FE',
            'range_start' => 1000,
            'range_end' => 2000,
            'next_number' => 1001,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
            'is_active' => true,
            'active_slot' => 1,
        ]);

        $result = $this->storeInvoice('electronic-sequence-1');
        $invoice = Invoice::query()->findOrFail($result['id']);

        $this->assertSame('FE1001', $invoice->invoice_number);
        $this->assertSame('electronic', $invoice->numbering_type);
        $this->assertSame(1001, $invoice->consecutive_number);
        $this->assertSame($range->id, $invoice->invoice_number_range_id);
        $this->assertSame(1002, $range->fresh()->next_number);

        $retry = app(InvoiceRepository::class)->storeInvoice(['idempotency_key' => 'electronic-sequence-1']);
        $this->assertFalse($retry['created']);
        $this->assertSame(1002, $range->fresh()->next_number);
    }

    public function test_electronic_invoice_is_rejected_without_a_usable_resolution(): void
    {
        $this->actingAs($this->user);
        getSchool($this->user)->forceFill(['electronic_invoicing_enabled' => true])->save();

        try {
            $this->storeInvoice('electronic-without-range');
            $this->fail('Expected invoice numbering validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'La escuela no tiene una resolución de facturación activa, vigente y con números disponibles.',
                $exception->errors()['invoice_number'][0]
            );
        }

        $this->assertDatabaseMissing('invoices', ['idempotency_key' => 'electronic-without-range']);
    }

    public function test_school_manages_ranges_but_only_super_admin_enables_electronic_invoicing(): void
    {
        $this->actingAs($this->user);

        $create = $this->postJson('/api/v2/admin/invoice-number-ranges', [
            'resolution_number' => '18764012345678',
            'resolution_date' => now()->subDay()->toDateString(),
            'prefix' => 'fe',
            'range_start' => 1,
            'range_end' => 500,
            'next_number' => 25,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
            'technical_key' => 'clave-secreta',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.prefix', 'FE')
            ->assertJsonPath('data.next_number', 25)
            ->assertJsonPath('data.has_technical_key', true)
            ->assertJsonMissingPath('data.technical_key');
        $rangeId = $create->json('data.id');

        $this->patchJson("/api/v2/admin/invoice-number-ranges/{$rangeId}/activate")
            ->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->patchJson('/api/v2/admin/invoice-numbering/electronic-mode', ['enabled' => true])
            ->assertForbidden();

        $superAdmin = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => 'invoice-superadmin@example.test',
        ], [User::SUPER_ADMIN]);
        $this->actingAs($superAdmin);

        $this->patchJson('/api/v2/admin/invoice-numbering/electronic-mode', ['enabled' => true])
            ->assertOk()
            ->assertJsonPath('electronic_invoicing_enabled', true);

        $this->assertTrue((bool) getSchool($superAdmin)->fresh()->electronic_invoicing_enabled);
    }

    public function test_payment_cannot_change_an_electronic_invoice_issue_date(): void
    {
        $this->actingAs($this->user);
        getSchool($this->user)->forceFill(['electronic_invoicing_enabled' => true])->save();
        InvoiceNumberRange::query()->create([
            'school_id' => $this->school['id'],
            'resolution_number' => '18764055555555',
            'resolution_date' => now()->subDay()->toDateString(),
            'prefix' => 'FV',
            'range_start' => 1,
            'range_end' => 100,
            'next_number' => 1,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
            'is_active' => true,
            'active_slot' => 1,
        ]);
        $result = $this->storeInvoice('electronic-payment-date');
        $invoice = Invoice::query()->with('items')->findOrFail($result['id']);
        $originalIssueDate = $invoice->issue_date->toDateString();

        $this->postJson("/api/v2/invoices/{$invoice->id}/payment", [
            'amount' => 25000,
            'idempotency_key' => 'electronic-payment-date-payment',
            'payment_method' => 'cash',
            'issue_date' => now()->addDays(5)->toDateString(),
            'payment_date' => now()->toDateString(),
            'paid_items' => [$invoice->items->first()->id],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('issue_date');

        $this->assertSame($originalIssueDate, $invoice->fresh()->issue_date->toDateString());
    }

    public function test_ranges_do_not_overlap_and_activation_is_isolated_by_school(): void
    {
        $this->actingAs($this->user);
        $payload = [
            'resolution_number' => '18764011111111',
            'resolution_date' => now()->subDay()->toDateString(),
            'prefix' => 'FE',
            'range_start' => 1,
            'range_end' => 100,
            'next_number' => 1,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
        ];
        $firstId = $this->postJson('/api/v2/admin/invoice-number-ranges', $payload)
            ->assertCreated()->json('data.id');

        $this->postJson('/api/v2/admin/invoice-number-ranges', array_merge($payload, [
            'resolution_number' => '18764022222222',
            'range_start' => 50,
            'range_end' => 150,
            'next_number' => 50,
        ]))->assertUnprocessable()->assertJsonValidationErrors('range_start');

        $secondId = $this->postJson('/api/v2/admin/invoice-number-ranges', array_merge($payload, [
            'resolution_number' => '18764033333333',
            'range_start' => 101,
            'range_end' => 200,
            'next_number' => 101,
        ]))->assertCreated()->json('data.id');

        $this->patchJson("/api/v2/admin/invoice-number-ranges/{$firstId}/activate")->assertOk();
        $this->patchJson("/api/v2/admin/invoice-number-ranges/{$secondId}/activate")->assertOk();
        $this->assertFalse(InvoiceNumberRange::query()->findOrFail($firstId)->is_active);
        $this->assertTrue(InvoiceNumberRange::query()->findOrFail($secondId)->is_active);

        $otherSchool = School::factory()->create();
        $foreignRange = InvoiceNumberRange::query()->create($payload + ['school_id' => $otherSchool->id]);
        $this->patchJson("/api/v2/admin/invoice-number-ranges/{$foreignRange->id}/activate")->assertNotFound();
    }

    private function storeInvoice(string $idempotencyKey): array
    {
        $player = Player::factory()->create(['school_id' => $this->school['id']]);
        $trainingGroup = getSchool($this->user)->trainingGroups()->firstOrFail();
        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->toDateString(),
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
        ]);

        return app(InvoiceRepository::class)->storeInvoice([
            'idempotency_key' => $idempotencyKey,
            'inscription_id' => $inscription->id,
            'training_group_id' => $trainingGroup->id,
            'year' => now()->year,
            'student_name' => $player->full_names,
            'due_date' => now()->addWeek()->toDateString(),
            'school_id' => $this->school['id'],
            'items' => [[
                'type' => 'additional',
                'description' => 'Concepto de prueba',
                'quantity' => 1,
                'unit_price' => 25000,
            ]],
        ]);
    }
}
