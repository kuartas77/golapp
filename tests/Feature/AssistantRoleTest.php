<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\InvoiceCustomItem;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use App\Models\SchoolUser;
use App\Models\User;
use Tests\TestCase;

final class AssistantRoleTest extends TestCase
{
    private User $assistant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assistant = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => 'assistant@example.test',
        ], [User::ASSISTANT]);

        SchoolUser::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $this->assistant->id,
        ]);
    }

    public function test_status_catalog_describes_the_assistant_monthly_payment_policy(): void
    {
        $this->actingAs($this->assistant)
            ->getJson('/api/v2/payments/status-catalog')
            ->assertOk()
            ->assertJsonPath('capabilities.bulk_update', false)
            ->assertJsonPath('capabilities.source_statuses', [Payment::$debt, Payment::$paid_])
            ->assertJsonPath('capabilities.target_statuses', [
                Payment::$paid,
                Payment::$paid_cash,
                Payment::$paid_deposit,
                Payment::$paid_,
            ])
            ->assertJsonPath('capabilities.fields.0', 'enrollment');
    }

    public function test_user_form_role_catalog_exposes_the_assistant_without_a_fixed_id(): void
    {
        $assistantRoleId = $this->assistant->roles()->firstOrFail()->id;

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/users/role-options')
            ->assertOk()
            ->assertJsonFragment([
                'value' => $assistantRoleId,
                'name' => User::ASSISTANT,
                'label' => 'Auxiliar administrativo',
            ]);
    }

    public function test_changing_a_user_to_assistant_refreshes_the_authenticated_context(): void
    {
        $target = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => 'target.assistant@gmail.com',
        ], [User::INSTRUCTOR]);
        SchoolUser::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $target->id,
        ]);
        $assistantRoleId = $this->assistant->roles()->firstOrFail()->id;

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'rol_id' => $assistantRoleId,
            ])
            ->assertOk()
            ->assertJsonPath('data.roles.0', User::ASSISTANT);

        $this->actingAs($target->fresh())
            ->getJson('/api/v2/user')
            ->assertOk()
            ->assertJsonPath('data.roles.0', User::ASSISTANT);
    }

    public function test_assistant_can_close_due_enrollment_and_month_once_and_the_changes_are_audited(): void
    {
        [$inscription, $payment] = $this->paymentFixture();

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'enrollment',
                'enrollment' => Payment::$paid,
                'enrollment_amount' => 72000,
            ])
            ->assertOk()
            ->assertJsonPath('data.enrollment', Payment::$paid)
            ->assertJsonPath('data.enrollment_amount', 72000);

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'january',
                'january' => Payment::$paid_cash,
                'january_amount' => 55000,
            ])
            ->assertOk()
            ->assertJsonPath('data.january', Payment::$paid_cash);

        $this->assertDatabaseHas('payment_change_logs', [
            'payment_id' => $payment->id,
            'inscription_id' => $inscription->id,
            'changed_by' => $this->assistant->id,
            'field' => 'enrollment',
            'old_status' => Payment::$debt,
            'new_status' => Payment::$paid,
            'old_amount' => 70000,
            'new_amount' => 72000,
            'source' => 'assistant',
        ]);

        $this->assertDatabaseHas('payment_change_logs', [
            'payment_id' => $payment->id,
            'inscription_id' => $inscription->id,
            'changed_by' => $this->assistant->id,
            'field' => 'january',
            'old_status' => Payment::$debt,
            'new_status' => Payment::$paid_cash,
            'source' => 'assistant',
        ]);

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'january',
                'january' => Payment::$paid_deposit,
                'january_amount' => 56000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('january');

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'enrollment',
                'enrollment' => Payment::$paid_cash,
                'enrollment_amount' => 73000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('enrollment');
    }

    public function test_assistant_can_update_a_partial_payment_until_it_is_fully_paid(): void
    {
        [$inscription, $payment] = $this->paymentFixture();
        $payment->update([
            'february' => Payment::$paid_,
            'february_amount' => 20000,
        ]);

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'february',
                'february' => Payment::$paid_,
                'february_amount' => 30000,
            ])
            ->assertOk()
            ->assertJsonPath('data.february', Payment::$paid_)
            ->assertJsonPath('data.february_amount', 30000);

        $this->assertDatabaseHas('payment_change_logs', [
            'payment_id' => $payment->id,
            'inscription_id' => $inscription->id,
            'changed_by' => $this->assistant->id,
            'field' => 'february',
            'old_status' => Payment::$paid_,
            'new_status' => Payment::$paid_,
            'old_amount' => 20000,
            'new_amount' => 30000,
            'source' => 'assistant',
        ]);

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'february',
                'february' => Payment::$paid_cash,
                'february_amount' => 50000,
            ])
            ->assertOk()
            ->assertJsonPath('data.february', Payment::$paid_cash)
            ->assertJsonPath('data.february_amount', 50000);

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'february',
                'february' => Payment::$paid_deposit,
                'february_amount' => 50000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('february');
    }

    public function test_assistant_cannot_edit_full_rows_bulk_rows_or_retired_inscriptions(): void
    {
        [$inscription, $payment] = $this->paymentFixture();

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'january' => Payment::$paid,
                'january_amount' => 50000,
            ])->assertUnprocessable()->assertJsonValidationErrors('column');

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'january',
                'january' => Payment::$paid,
                'january_amount' => 50000,
                'february' => Payment::$paid,
                'february_amount' => 50000,
            ])->assertUnprocessable()->assertJsonValidationErrors('february');

        $this->actingAs($this->assistant)
            ->postJson('/api/v2/payments/bulk-update', [])->assertForbidden();

        $inscription->delete();
        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'february',
                'february' => Payment::$paid,
                'february_amount' => 50000,
            ])->assertUnprocessable();
    }

    public function test_assistant_adds_catalog_charges_but_cannot_edit_saved_charges(): void
    {
        [$inscription] = $this->paymentFixture();
        $item = InvoiceCustomItem::query()->create([
            'school_id' => $this->school['id'],
            'type' => 'OTHER',
            'name' => 'Transporte',
            'unit_price' => 35000,
        ]);
        $payload = ['charges' => [[
            'invoice_custom_item_id' => $item->id,
            'value' => 32000,
            'due_date' => now()->addWeek()->toDateString(),
        ]]];

        $response = $this->actingAs($this->assistant)
            ->postJson("/api/v2/inscriptions/{$inscription->id}/custom-charges", $payload)
            ->assertCreated()
            ->assertJsonPath('data.0.name', 'Transporte')
            ->assertJsonPath('data.0.status', 'pending');

        $chargeId = $response->json('data.0.id');

        $this->actingAs($this->assistant)
            ->postJson("/api/v2/inscriptions/{$inscription->id}/custom-charges", $payload)
            ->assertUnprocessable();
        $this->actingAs($this->assistant)
            ->putJson("/api/v2/admin/inscription-custom-charges/{$chargeId}", ['value' => 1])
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->deleteJson("/api/v2/admin/inscription-custom-charges/{$chargeId}")
            ->assertForbidden();
    }

    public function test_assistant_summary_is_read_only_and_does_not_serialize_sports_data(): void
    {
        [$inscription] = $this->paymentFixture();

        $this->actingAs($this->assistant)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/summary")
            ->assertOk()
            ->assertJsonPath('data.can_edit', false)
            ->assertJsonPath('data.capabilities.can_edit_payments', false)
            ->assertJsonPath('data.capabilities.can_view_sports', false)
            ->assertJsonPath('data.attendance', [])
            ->assertJsonPath('data.evaluations', []);

        $this->actingAs($this->assistant)
            ->getJson('/api/v2/reports/assists')
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->getJson('/api/v2/reports/payments')
            ->assertOk();
    }

    public function test_assistant_can_edit_existing_inscriptions_but_cannot_create_or_retire_them(): void
    {
        [$inscription] = $this->paymentFixture();
        $group = getSchool($this->assistant)->trainingGroups()->firstOrFail();

        $this->actingAs($this->assistant)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/edit")
            ->assertOk()
            ->assertJsonPath('id', $inscription->id);

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/inscriptions/{$inscription->id}", [
                'id' => $inscription->id,
                'player_id' => $inscription->player_id,
                'unique_code' => $inscription->unique_code,
                'start_date' => now()->startOfYear()->toDateString(),
                'training_group_id' => $group->id,
                'complementary_group_ids' => [],
                'competition_groups' => [],
                'photos' => true,
                'copy_identification_document' => true,
                'eps_certificate' => true,
                'medic_certificate' => true,
                'study_certificate' => false,
                'overalls' => false,
                'ball' => false,
                'bag' => false,
                'presentation_uniform' => false,
                'competition_uniform' => false,
                'tournament_pay' => false,
                'scholarship' => false,
                'pre_inscription' => true,
                'monthly_payment_type' => Setting::MONTHLY_PAYMENT,
                'recalculate_monthly_payments' => false,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'pre_inscription' => true,
            'eps_certificate' => true,
        ]);

        $otherSchool = School::factory()->create();
        $otherPlayer = Player::factory()->create(['school_id' => $otherSchool->id]);
        $otherInscription = Inscription::factory()->create([
            'school_id' => $otherSchool->id,
            'player_id' => $otherPlayer->id,
            'unique_code' => $otherPlayer->unique_code,
            'competition_group_id' => null,
            'year' => now()->year,
        ]);

        $this->actingAs($this->assistant)
            ->getJson("/api/v2/inscriptions/{$otherInscription->id}/edit")
            ->assertNotFound();
        $this->actingAs($this->assistant)
            ->putJson("/api/v2/inscriptions/{$otherInscription->id}", [
                'id' => $otherInscription->id,
                'player_id' => $inscription->player_id,
                'unique_code' => $inscription->unique_code,
                'start_date' => now()->startOfYear()->toDateString(),
                'training_group_id' => $group->id,
                'complementary_group_ids' => [],
                'competition_groups' => [],
                'scholarship' => false,
                'pre_inscription' => false,
                'monthly_payment_type' => Setting::MONTHLY_PAYMENT,
            ])
            ->assertNotFound();

        $this->actingAs($this->assistant)
            ->postJson('/api/v2/inscriptions', [])
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->deleteJson("/api/v2/inscriptions/{$inscription->id}")
            ->assertForbidden();
    }

    public function test_assistant_spa_routes_allow_financial_work_and_reject_sports_modules(): void
    {
        $this->actingAs($this->assistant)
            ->get('/deportistas')
            ->assertOk();
        $this->actingAs($this->assistant)
            ->get('/mensualidades')
            ->assertOk();
        $this->actingAs($this->assistant)
            ->get('/informes/pagos')
            ->assertOk();
        $this->actingAs($this->assistant)
            ->get('/asistencias')
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->get('/informes/asistencias')
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->get('/mensualidades/notificaciones-deuda')
            ->assertForbidden();
    }

    public function test_assistant_can_query_receipts_and_invoice_details_but_not_mutate_invoices(): void
    {
        $this->actingAs($this->assistant)
            ->getJson('/api/v2/payments/monthly-receipts')
            ->assertOk();

        // A missing invoice reaches the read controller; mutation routes stop at authorization.
        $this->actingAs($this->assistant)
            ->getJson('/api/v2/invoices/999999')
            ->assertNotFound();
        $this->actingAs($this->assistant)
            ->postJson('/api/v2/invoices', [])
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->postJson('/api/v2/invoices/999999/payment', [])
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->deleteJson('/api/v2/invoices/999999')
            ->assertForbidden();
    }

    private function paymentFixture(): array
    {
        $group = getSchool($this->assistant)->trainingGroups()->firstOrFail();
        $player = Player::factory()->create(['school_id' => $this->school['id']]);
        $inscription = Inscription::factory()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => now()->year,
        ]);
        $payment = Payment::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'year' => now()->year,
        ], [
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'unique_code' => $player->unique_code,
            'enrollment' => Payment::$debt,
            'january' => Payment::$debt,
            'february' => Payment::$debt,
            'march' => Payment::$debt,
            'april' => Payment::$debt,
            'may' => Payment::$debt,
            'june' => Payment::$debt,
            'july' => Payment::$debt,
            'august' => Payment::$debt,
            'september' => Payment::$debt,
            'october' => Payment::$debt,
            'november' => Payment::$debt,
            'december' => Payment::$debt,
            'enrollment_amount' => 70000,
            'january_amount' => 50000,
            'february_amount' => 50000,
            'march_amount' => 50000,
            'april_amount' => 50000,
            'may_amount' => 50000,
            'june_amount' => 50000,
            'july_amount' => 50000,
            'august_amount' => 50000,
            'september_amount' => 50000,
            'october_amount' => 50000,
            'november_amount' => 50000,
            'december_amount' => 50000,
        ]);

        return [$inscription, $payment];
    }
}
