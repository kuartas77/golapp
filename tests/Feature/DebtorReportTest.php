<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Service\Reports\DebtorReportService;
use Tests\TestCase;

final class DebtorReportTest extends TestCase
{
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

        Invoice::query()->create([
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

        Invoice::query()->create([
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

        $rows = app(DebtorReportService::class)->rows([
            'school_id' => $this->school['id'],
            'year' => 2026,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(50000.0, $rows->first()['monthly_debt']);
        $this->assertSame(75000.0, $rows->first()['invoice_debt']);
        $this->assertSame(125000.0, $rows->first()['total_debt']);
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
        $this->assertSame(0.0, $rows->first()['monthly_debt']);
        $this->assertSame(60000.0, $rows->first()['invoice_debt']);
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
