<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Service\Reports\DebtorReportService;
use Tests\TestCase;

final class DebtorReportTest extends TestCase
{
    public function testDebtorReportOnlyShowsDebtItemAmountsWhenTotalsAreEnabled(): void
    {
        $data = [
            'school' => (object) [
                'logo_local' => '',
                'name' => 'Escuela test',
            ],
            'rows' => collect([[
                'unique_code' => '1001',
                'student_name' => 'Ana Torres',
                'category' => 'Sub 10',
                'debt_items' => [[
                    'label' => 'Mensualidad Enero',
                    'amount' => 50000,
                ], [
                    'label' => 'Uniforme',
                    'amount' => 25000,
                ]],
                'total_debt' => 75000,
            ]]),
            'date' => '19-06-2026 10:00:00',
            'year' => 2026,
            'group' => 'Todos los grupos',
        ];

        $withoutTotals = view('templates.pdf.debtors', $data + ['showTotalDebt' => false])->render();
        $withTotals = view('templates.pdf.debtors', $data + ['showTotalDebt' => true])->render();
        $withoutTotalsText = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($withoutTotals)));
        $withTotalsText = preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($withTotals)));

        $this->assertStringNotContainsString('(50.000)', $withoutTotals);
        $this->assertStringContainsString('(50.000)', $withTotals);
        $this->assertStringContainsString('Mensualidad Enero, Uniforme', $withoutTotalsText);
        $this->assertStringContainsString('Mensualidad Enero (50.000), Uniforme (25.000)', $withTotalsText);
    }

    public function testDebtorReportConsolidatesMonthlyAndInvoiceDebts(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, '1001', 'Ana', 'Torres');

        $this->resetPayment($this->paymentForInscription($inscription))->update([
            'january' => Payment::$debt,
            'january_amount' => 50000,
            'february' => Payment::$paid,
            'february_amount' => 50000,
            'march' => Payment::$pending,
            'march_amount' => 50000,
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'FAC-DEBT-001',
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => 2026,
            'student_name' => 'Ana Torres',
            'total_amount' => 100000,
            'paid_amount' => 25000,
            'status' => 'partial',
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-31',
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
        ]);

        $invoice->items()->create([
            'type' => 'additional',
            'description' => 'Uniforme',
            'quantity' => 1,
            'unit_price' => 75000,
            'is_paid' => false,
        ]);

        $paidInvoice = Invoice::query()->create([
            'invoice_number' => 'FAC-DEBT-PAID',
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => 2026,
            'student_name' => 'Ana Torres',
            'total_amount' => 70000,
            'paid_amount' => 70000,
            'status' => 'paid',
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-31',
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
        ]);

        $paidInvoice->items()->create([
            'type' => 'additional',
            'description' => 'No debe salir',
            'quantity' => 1,
            'unit_price' => 70000,
            'is_paid' => true,
        ]);
        $paidInvoice->forceFill([
            'total_amount' => 70000,
            'paid_amount' => 70000,
            'status' => 'paid',
        ])->save();

        $rows = app(DebtorReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(125000.0, $rows->first()['total_debt']);
        $this->assertSame([
            'Mensualidades: Debe Enero',
            'FAC-DEBT-001 - Item: Uniforme',
        ], collect($rows->first()['debt_items'])->pluck('label')->all());
    }

    public function testDebtorReportDoesNotDuplicateMonthlyDebtAlreadyInPendingInvoice(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, '1002', 'Luis', 'Rios');

        $payment = $this->resetPayment($this->paymentForInscription($inscription));
        $payment->update([
            'january' => Payment::$debt,
            'january_amount' => 60000,
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'FAC-DEBT-002',
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => 2026,
            'student_name' => 'Luis Rios',
            'total_amount' => 60000,
            'paid_amount' => 0,
            'status' => 'pending',
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-31',
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
        ]);

        $invoice->items()->create([
            'type' => 'monthly',
            'description' => 'Enero',
            'quantity' => 1,
            'unit_price' => 60000,
            'month' => 'january',
            'payment_id' => $payment->id,
            'is_paid' => false,
        ]);

        $rows = app(DebtorReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(['FAC-DEBT-002 - Mensualidad: Enero'], collect($rows->first()['debt_items'])->pluck('label')->all());
        $this->assertSame(60000.0, $rows->first()['total_debt']);
    }

    public function testDebtorReportFiltersByTrainingGroup(): void
    {
        $this->actingAs($this->user);
        $includedGroup = $this->defaultTrainingGroup();
        $otherGroup = TrainingGroup::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Sub 12',
            'stage' => 'A',
            'year' => '2014',
            'category' => 'Sub 12',
            'days' => 'Lunes',
            'schedules' => '10:00AM - 11:00AM',
        ]);

        $included = $this->createInscriptionForReport($includedGroup, '1003', 'Mia', 'Lopez');
        $excluded = $this->createInscriptionForReport($otherGroup, '1004', 'Sara', 'Diaz');

        foreach ([[$included, $includedGroup], [$excluded, $otherGroup]] as [$inscription]) {
            $this->resetPayment($this->paymentForInscription($inscription))->update([
                'january' => Payment::$debt,
                'january_amount' => 50000,
            ]);
        }

        $rows = app(DebtorReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
            'training_group_id' => $includedGroup->id,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('1003', $rows->first()['unique_code']);
    }

    public function testDebtorReportOrdersRowsByCategoryUsingNaturalNumericOrder(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();

        foreach ([
            ['code' => '1010', 'name' => 'Ana', 'category' => 'SUB-10'],
            ['code' => '1003', 'name' => 'Beto', 'category' => 'SUB-3'],
            ['code' => '1001', 'name' => 'Carlos', 'category' => 'SUB-1'],
        ] as $playerData) {
            $inscription = $this->createInscriptionForReport(
                $group,
                $playerData['code'],
                $playerData['name'],
                'Test'
            );
            $inscription->update(['category' => $playerData['category']]);

            $this->resetPayment($this->paymentForInscription($inscription))->update([
                'january' => Payment::$debt,
                'january_amount' => 50000,
            ]);
        }

        $rows = app(DebtorReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertSame(['SUB-1', 'SUB-3', 'SUB-10'], $rows->pluck('category')->all());
    }

    public function testDebtorReportIncludesDueCustomChargesThatAreNotInvoiced(): void
    {
        $this->actingAs($this->user);
        $group = $this->defaultTrainingGroup();
        $inscription = $this->createInscriptionForReport($group, '1005', 'Noa', 'Vargas');

        $this->resetPayment($this->paymentForInscription($inscription));

        InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $inscription->player_id,
            'name' => 'Guayos',
            'value' => 90000,
            'status' => InscriptionCustomCharge::STATUS_DUE,
            'due_date' => '2026-03-15',
        ]);

        InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $inscription->player_id,
            'name' => 'Pendiente',
            'value' => 30000,
            'status' => InscriptionCustomCharge::STATUS_PENDING,
            'due_date' => '2026-03-15',
        ]);

        $rows = app(DebtorReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(['Guayos'], collect($rows->first()['debt_items'])->pluck('label')->all());
        $this->assertSame(90000.0, $rows->first()['total_debt']);
    }

    private function defaultTrainingGroup(): TrainingGroup
    {
        return TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
    }

    private function createInscriptionForReport(TrainingGroup $group, string $uniqueCode, string $names, string $lastNames): Inscription
    {
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => $uniqueCode,
            'names' => $names,
            'last_names' => $lastNames,
        ]);

        return Inscription::factory()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $uniqueCode,
            'year' => 2026,
            'training_group_id' => $group->id,
            'competition_group_id' => $this->createCompetitionGroupForReport()->id,
            'category' => 'Sub 10',
        ]);
    }

    private function createCompetitionGroupForReport(): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Torneo test',
        ]);

        return CompetitionGroup::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Competencia test',
            'year' => '2026',
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => 'Sub 10',
        ]);
    }

    private function paymentForInscription(Inscription $inscription): Payment
    {
        return Payment::query()
            ->where('year', 2026)
            ->where('training_group_id', $inscription->training_group_id)
            ->where('inscription_id', $inscription->id)
            ->firstOrFail();
    }

    private function resetPayment(Payment $payment): Payment
    {
        $values = [];

        foreach (Payment::paymentFields() as $field) {
            $values[$field] = Payment::$pending;
            $values[Payment::amountFieldFor($field)] = 0;
        }

        $payment->update($values);

        return $payment->fresh();
    }
}
