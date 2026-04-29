<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentReceived;
use App\Models\Player;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InvoiceItemsTest extends TestCase
{
    public function test_legacy_invoice_items_url_redirects_to_spa_route(): void
    {
        $this->actingAs($this->user)
            ->get('/items/invoices')
            ->assertRedirect('/facturas/items');
    }

    public function test_invoice_items_index_returns_datatable_payload(): void
    {
        $fixture = $this->createInvoiceItemFixture();
        $params = [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'columns' => [
                ['data' => 'invoice.invoice_number', 'name' => 'invoice.invoice_number', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'created_at', 'name' => 'invoice_items.created_at', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'invoice.student_name', 'name' => 'invoice.student_name', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'type', 'name' => 'type', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'description', 'name' => 'description', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'payment_method', 'name' => 'payment_method', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'quantity', 'name' => 'quantity', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'unit_price', 'name' => 'unit_price', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'total', 'name' => 'total', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'is_paid', 'name' => 'is_paid', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
        ];

        $this->actingAs($this->user)
            ->getJson('/api/v2/invoices/items/invoices?'.http_build_query($params))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', $fixture['item']->description)
            ->assertJsonPath('data.0.invoice.invoice_number', $fixture['invoice']->invoice_number);
    }

    private function createInvoiceItemFixture(): array
    {
        $school = School::query()->findOrFail($this->school['id']);
        $trainingGroup = $school->trainingGroups()->firstOrFail();

        $player = Player::factory()->create([
            'school_id' => $school->id,
            'email' => 'invoice-items@example.test',
        ]);

        $inscription = Inscription::withoutEvents(fn () => Inscription::query()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'start_date' => now()->toDateString(),
            'category' => 'Sub 10',
            'photos' => false,
            'scholarship' => false,
            'copy_identification_document' => false,
            'eps_certificate' => false,
            'medic_certificate' => false,
            'study_certificate' => false,
            'overalls' => false,
            'ball' => false,
            'bag' => false,
            'presentation_uniform' => false,
            'competition_uniform' => false,
            'tournament_pay' => false,
            'period_one' => null,
            'period_two' => null,
            'period_three' => null,
            'period_four' => null,
            'school_id' => $school->id,
        ]));

        $invoice = Invoice::query()->create([
            'invoice_number' => 'FAC-ITEM-TEST',
            'inscription_id' => $inscription->id,
            'training_group_id' => $trainingGroup->id,
            'year' => now()->year,
            'student_name' => $player->full_names,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'pending',
            'school_id' => $school->id,
            'created_by' => $this->user->id,
            'notes' => 'Factura de prueba',
        ]);

        $paymentReceived = PaymentReceived::query()->create([
            'invoice_id' => $invoice->id,
            'amount' => 0,
            'payment_method' => 'cash',
            'reference' => 'TEST-ITEMS',
            'payment_date' => now()->toDateString(),
            'notes' => 'Pago de prueba',
            'school_id' => $school->id,
            'created_by' => $this->user->id,
        ]);

        Schema::disableForeignKeyConstraints();

        DB::table('invoice_items')->insert([
            'invoice_id' => $invoice->id,
            'type' => 'additional',
            'description' => 'Item de prueba',
            'quantity' => 2,
            'unit_price' => 25000,
            'total' => 50000,
            'month' => null,
            'payment_id' => null,
            'is_paid' => false,
            'payment_received_id' => $paymentReceived->id,
            'uniform_request_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::enableForeignKeyConstraints();

        $item = InvoiceItem::query()->firstOrFail();

        return compact('invoice', 'item');
    }
}
