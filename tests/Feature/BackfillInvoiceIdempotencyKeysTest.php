<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Player;
use App\Models\TrainingGroup;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BackfillInvoiceIdempotencyKeysTest extends TestCase
{
    public function testCommandBackfillsMissingInvoiceIdempotencyKey(): void
    {
        [$invoice, $payment] = $this->createInvoiceWithMonthlyItem();

        $this->artisan('invoices:backfill-idempotency-keys', ['--period' => '2026-05'])
            ->expectsOutput('Periodo protegido: 2026-05')
            ->expectsOutput('Facturas actualizadas: 1')
            ->expectsOutput('Duplicados omitidos: 0')
            ->expectsOutput('Sin items omitidas: 0')
            ->assertExitCode(0);

        $expectedKey = app(InvoiceRepository::class)->buildAutoInvoiceIdempotencyKey([
            'school_id' => $invoice->school_id,
            'inscription_id' => $invoice->inscription_id,
            'training_group_id' => $invoice->training_group_id,
            'year' => $invoice->year,
            'items' => [[
                'type' => 'monthly',
                'month' => 'january',
                'payment_id' => $payment->id,
                'uniform_request_id' => null,
                'custom_charge_id' => null,
                'quantity' => 1,
                'unit_price' => '50000.00',
            ]],
        ], $invoice->issue_date);

        $this->assertSame($expectedKey, $invoice->fresh()->idempotency_key);
    }

    public function testCommandSkipsDuplicateGeneratedKeys(): void
    {
        [$firstInvoice, $payment] = $this->createInvoiceWithMonthlyItem('FAC-BACKFILL-1');
        $secondInvoice = $this->createDuplicateInvoiceWithMonthlyItem($firstInvoice, $payment, 'FAC-BACKFILL-2');

        $this->artisan('invoices:backfill-idempotency-keys', ['--period' => '2026-05'])
            ->expectsOutput('Periodo protegido: 2026-05')
            ->expectsOutput('Facturas actualizadas: 1')
            ->expectsOutput('Duplicados omitidos: 1')
            ->expectsOutput('Sin items omitidas: 0')
            ->assertExitCode(0);

        $this->assertNotNull($firstInvoice->fresh()->idempotency_key);
        $this->assertNull($secondInvoice->fresh()->idempotency_key);
    }

    private function createInvoiceWithMonthlyItem(string $invoiceNumber = 'FAC-BACKFILL'): array
    {
        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->firstOrFail();

        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
        ]);

        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
        ]);

        $payment = Payment::query()
            ->where('inscription_id', $inscription->id)
            ->where('year', 2026)
            ->firstOrFail();

        $invoice = Invoice::query()->create([
            'invoice_number' => $invoiceNumber,
            'inscription_id' => $inscription->id,
            'training_group_id' => $trainingGroup->id,
            'year' => 2026,
            'student_name' => $player->full_names,
            'issue_date' => '2026-05-10',
            'due_date' => '2026-05-25',
            'status' => 'pending',
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
            'notes' => 'Generada automaticamente',
        ]);

        Schema::disableForeignKeyConstraints();

        try {
            $invoice->items()->create([
                'type' => 'monthly',
                'description' => 'Enero',
                'quantity' => 1,
                'unit_price' => 50000,
                'month' => 'january',
                'payment_id' => $payment->id,
                'is_paid' => false,
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return [$invoice->fresh('items'), $payment];
    }

    private function createDuplicateInvoiceWithMonthlyItem(Invoice $sourceInvoice, Payment $payment, string $invoiceNumber): Invoice
    {
        $invoice = Invoice::query()->create([
            'invoice_number' => $invoiceNumber,
            'inscription_id' => $sourceInvoice->inscription_id,
            'training_group_id' => $sourceInvoice->training_group_id,
            'year' => $sourceInvoice->year,
            'student_name' => $sourceInvoice->student_name,
            'issue_date' => $sourceInvoice->issue_date->toDateString(),
            'due_date' => $sourceInvoice->due_date->toDateString(),
            'status' => 'pending',
            'school_id' => $sourceInvoice->school_id,
            'created_by' => $this->user->id,
            'notes' => 'Generada automaticamente',
        ]);

        Schema::disableForeignKeyConstraints();

        try {
            $invoice->items()->create([
                'type' => 'monthly',
                'description' => 'Enero',
                'quantity' => 1,
                'unit_price' => 50000,
                'month' => 'january',
                'payment_id' => $payment->id,
                'is_paid' => false,
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return $invoice->fresh('items');
    }
}
