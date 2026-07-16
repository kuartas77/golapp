<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Player;
use App\Models\PlayerCreditMovement;
use App\Models\School;
use App\Models\TrainingGroup;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PlayerFinancialClearanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-07 10:30:00');
        $school = School::findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.players'] = true;
        $school->forceFill(['school_permissions' => $permissions])->save();
        School::forgetCachedSchool($school->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $school = School::find($this->school['id']);
        if ($school) {
            $permissions = $school->getResolvedSchoolPermissions();
            $permissions['school.module.players'] = true;
            $school->forceFill(['school_permissions' => $permissions])->save();
            School::forgetCachedSchool($school->id);
        }

        parent::tearDown();
    }

    public function test_school_can_verify_and_generate_clearance_for_player_without_debts(): void
    {
        $player = $this->createPlayerWithInscription('CLEAR-001', 2026);

        $this->actingAs($this->user)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertOk()
            ->assertJsonPath('eligible', true)
            ->assertJsonPath('total_debt', 0)
            ->assertJsonPath('credit_balance', 0)
            ->assertJsonPath('has_credit_balance', false)
            ->assertJsonCount(0, 'debts');

        $this->actingAs($this->user)
            ->get("/api/v2/players/{$player->unique_code}/financial-clearance/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="Paz y salvo CLEAR-001.pdf"');
    }

    public function test_clearance_template_does_not_display_credit_balance(): void
    {
        $html = view('templates.pdf.player_financial_clearance', [
            'school' => (object) [
                'name' => 'Escuela Demo',
                'logo_local' => '',
                'address' => 'Calle 1',
                'phone' => '123',
                'email' => 'escuela@example.com',
                'email_info' => null,
                'agent' => 'Responsable Demo',
            ],
            'player' => (object) [
                'full_names' => 'Laura Gómez',
                'identification_document' => '1000',
                'unique_code' => 'CLEAR-HTML',
            ],
            'issuedAt' => now(),
        ])->render();

        $this->assertStringContainsString('CERTIFICADO DE PAZ Y SALVO', $html);
        $this->assertStringNotContainsString('saldo a favor disponible', $html);
        $this->assertStringNotContainsString('$45.000', $html);
    }

    public function test_clearance_status_reports_available_credit_balance_without_blocking(): void
    {
        $player = $this->createPlayerWithInscription('CLEAR-CREDIT', 2026);

        PlayerCreditMovement::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'type' => PlayerCreditMovement::TYPE_CREDIT,
            'amount' => 75000,
            'movement_date' => now()->toDateString(),
            'concept' => 'Anticipo',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertOk()
            ->assertJsonPath('eligible', true)
            ->assertJsonPath('total_debt', 0)
            ->assertJsonPath('credit_balance', 75000)
            ->assertJsonPath('has_credit_balance', true);
    }

    public function test_historical_debts_are_itemized_and_block_pdf_generation(): void
    {
        $player = $this->createPlayerWithInscription('CLEAR-002', 2025);
        $inscription = $player->inscriptions()->where('year', 2025)->firstOrFail();
        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

        $this->resetPayment($payment)->update([
            'year' => 2025,
            'january' => Payment::$debt,
            'january_amount' => 55000,
        ]);

        InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'name' => 'Uniforme de competencia',
            'value' => 85000,
            'status' => InscriptionCustomCharge::STATUS_DUE,
            'due_date' => '2025-10-15',
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertOk()
            ->assertJsonPath('eligible', false)
            ->assertJsonPath('total_debt', 140000)
            ->assertJsonPath('debts.0.year', 2025)
            ->assertJsonPath('debts.0.label', 'Enero')
            ->assertJsonPath('debts.1.label', 'Uniforme de competencia');

        $this->actingAs($this->user)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance/pdf")
            ->assertUnprocessable()
            ->assertJsonPath('eligible', false)
            ->assertJsonPath('total_debt', 140000);
    }

    public function test_future_obligations_do_not_block_and_invoiced_monthly_debt_is_not_duplicated(): void
    {
        $player = $this->createPlayerWithInscription('CLEAR-003', 2026);
        $inscription = $player->inscriptions()->firstOrFail();
        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();
        $this->resetPayment($payment)->update([
            'january' => Payment::$debt,
            'january_amount' => 60000,
        ]);

        $overdueInvoice = $this->createInvoice($inscription, 'FAC-CLEAR-1', '2026-01-31', 60000);
        $overdueInvoice->items()->create([
            'type' => 'monthly',
            'description' => 'Mensualidad enero',
            'quantity' => 1,
            'unit_price' => 60000,
            'month' => 'january',
            'payment_id' => $payment->id,
            'is_paid' => false,
        ]);

        $duplicateInvoice = $this->createInvoice($inscription, 'FAC-CLEAR-DUPLICATE', '2026-02-28', 60000);
        $duplicateInvoice->items()->create([
            'type' => 'monthly',
            'description' => 'Mensualidad enero',
            'quantity' => 1,
            'unit_price' => 60000,
            'month' => 'january',
            'payment_id' => $payment->id,
            'is_paid' => false,
        ]);

        $futureInvoice = $this->createInvoice($inscription, 'FAC-CLEAR-FUTURE', '2026-08-15', 90000);
        $futureInvoice->items()->create([
            'type' => 'additional',
            'description' => 'Concepto futuro',
            'quantity' => 1,
            'unit_price' => 90000,
            'is_paid' => false,
        ]);

        InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'name' => 'Cobro futuro',
            'value' => 45000,
            'status' => InscriptionCustomCharge::STATUS_DUE,
            'due_date' => '2026-08-20',
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertOk()
            ->assertJsonPath('eligible', false)
            ->assertJsonPath('total_debt', 60000)
            ->assertJsonCount(1, 'debts')
            ->assertJsonPath('debts.0.label', 'Mensualidad enero');
    }

    public function test_clearance_endpoints_enforce_school_scope_role_and_player_permission(): void
    {
        $player = $this->createPlayerWithInscription('CLEAR-004', 2026);
        [$otherSchool, $otherUser] = $this->createSchoolAndUser([
            'email' => 'other-clearance@example.com',
            'slug' => 'other-clearance',
        ]);
        $otherSchoolModel = School::findOrFail($otherSchool['id']);
        $otherPermissions = $otherSchoolModel->getResolvedSchoolPermissions();
        $otherPermissions['school.module.players'] = true;
        $otherSchoolModel->forceFill(['school_permissions' => $otherPermissions])->save();
        School::forgetCachedSchool($otherSchoolModel->id);

        $this->actingAs($otherUser)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertNotFound();

        $instructor = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => 'clearance-instructor@example.com',
        ], ['instructor']);

        $this->actingAs($instructor)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertForbidden();

        $school = School::findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.players'] = false;
        $school->forceFill(['school_permissions' => $permissions])->save();
        School::forgetCachedSchool($school->id);

        $this->actingAs($this->user)
            ->getJson("/api/v2/players/{$player->unique_code}/financial-clearance")
            ->assertForbidden();

        $this->assertNotSame($otherSchool['id'], $this->school['id']);
    }

    private function createPlayerWithInscription(string $uniqueCode, int $year): Player
    {
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => $uniqueCode,
            'names' => 'Laura',
            'last_names' => 'Gómez',
        ]);
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        Inscription::factory()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $uniqueCode,
            'year' => $year,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);

        $payment = Payment::query()->whereHas('inscription', fn ($query) => $query->where('player_id', $player->id))->firstOrFail();
        $this->resetPayment($payment);

        return $player->fresh();
    }

    private function createInvoice(Inscription $inscription, string $number, string $dueDate, int $amount): Invoice
    {
        return Invoice::query()->create([
            'invoice_number' => $number,
            'inscription_id' => $inscription->id,
            'training_group_id' => $inscription->training_group_id,
            'year' => $inscription->year,
            'student_name' => $inscription->player->full_names,
            'total_amount' => $amount,
            'paid_amount' => 0,
            'status' => 'pending',
            'issue_date' => '2026-01-01',
            'due_date' => $dueDate,
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
        ]);
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
