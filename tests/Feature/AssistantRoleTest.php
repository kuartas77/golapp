<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\InvoiceCustomItem;
use App\Models\Payment;
use App\Models\Player;
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
            ->assertJsonPath('capabilities.source_statuses', [Payment::$debt])
            ->assertJsonPath('capabilities.target_statuses', [
                Payment::$paid,
                Payment::$paid_cash,
                Payment::$paid_deposit,
                Payment::$paid_,
            ])
            ->assertJsonMissing(['value' => 'enrollment', 'number' => 0]);
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

    public function test_assistant_can_close_a_due_month_once_and_the_change_is_audited(): void
    {
        [$inscription, $payment] = $this->paymentFixture();

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
    }

    public function test_assistant_cannot_edit_enrollment_full_rows_bulk_rows_or_retired_inscriptions(): void
    {
        [$inscription, $payment] = $this->paymentFixture();

        $this->actingAs($this->assistant)
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'enrollment',
                'enrollment' => Payment::$paid,
                'enrollment_amount' => 70000,
            ])->assertUnprocessable();

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
            ->putJson("/api/v2/inscriptions/{$inscription->id}", [])
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->getJson('/api/v2/reports/assists')
            ->assertForbidden();
        $this->actingAs($this->assistant)
            ->getJson('/api/v2/reports/payments')
            ->assertOk();
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
