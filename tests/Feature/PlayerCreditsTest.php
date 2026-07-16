<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\PlayerCreditMovement;
use App\Models\School;
use App\Models\TrainingGroup;
use Tests\TestCase;

final class PlayerCreditsTest extends TestCase
{
    public function test_player_credits_permission_blocks_module_endpoints(): void
    {
        $school = School::findOrFail($this->school['id']);
        $this->setPlayerCreditsPermission($school, false);
        $player = $this->createPlayerForCredits();

        $this->actingAs($this->user)
            ->getJson('/api/v2/player-credits')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->postJson("/api/v2/player-credits/{$player->id}/movements", [
                'type' => 'credit',
                'amount' => 50000,
                'movement_date' => now()->toDateString(),
                'concept' => 'Anticipo',
            ])
            ->assertForbidden();
    }

    public function test_manual_credit_and_debit_update_player_balance(): void
    {
        $player = $this->createPlayerForCredits();

        $this->actingAs($this->user)
            ->postJson("/api/v2/player-credits/{$player->id}/movements", [
                'type' => 'credit',
                'amount' => 50000,
                'movement_date' => now()->toDateString(),
                'concept' => 'Anticipo',
            ])
            ->assertCreated()
            ->assertJsonPath('balance', 50000);

        $this->actingAs($this->user)
            ->postJson("/api/v2/player-credits/{$player->id}/movements", [
                'type' => 'debit',
                'amount' => 15000,
                'movement_date' => now()->toDateString(),
                'concept' => 'Descuento manual',
            ])
            ->assertCreated()
            ->assertJsonPath('balance', 35000);
    }

    public function test_search_finds_players_without_credit_movements_for_initial_load(): void
    {
        $player = $this->createPlayerForCredits([
            'names' => 'Carlos Andres',
            'last_names' => 'Restrepo Gomez',
            'unique_code' => 'CRE-FIRST-001',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/player-credits')
            ->assertOk()
            ->assertJsonMissing(['id' => $player->id]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/player-credits?search=Carlos%20Restrepo')
            ->assertOk()
            ->assertJsonFragment(['id' => $player->id]);
    }

    public function test_datatable_endpoint_paginates_credit_balances(): void
    {
        $first = $this->createPlayerForCredits(['names' => 'Saldo Mayor', 'unique_code' => 'CRE-DT-001']);
        $second = $this->createPlayerForCredits(['names' => 'Saldo Menor', 'unique_code' => 'CRE-DT-002']);

        foreach ([[$first, 80000], [$second, 20000]] as [$player, $amount]) {
            PlayerCreditMovement::query()->create([
                'school_id' => $this->school['id'],
                'player_id' => $player->id,
                'type' => PlayerCreditMovement::TYPE_CREDIT,
                'amount' => $amount,
                'movement_date' => now()->toDateString(),
                'concept' => 'Anticipo',
                'created_by' => $this->user->id,
            ]);
        }

        $this->actingAs($this->user)
            ->getJson('/api/v2/player-credits/datatable?'.http_build_query([
                'draw' => 3,
                'start' => 0,
                'length' => 1,
                'columns' => [
                    ['data' => 'full_names'],
                    ['data' => 'training_group'],
                    ['data' => 'credit_total'],
                    ['data' => 'debit_total'],
                    ['data' => 'balance'],
                ],
                'order' => [
                    ['column' => 4, 'dir' => 'desc'],
                ],
            ]))
            ->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('data.0.id', $first->id);
    }

    public function test_monthly_payment_with_player_credit_requires_sufficient_balance(): void
    {
        [$player, $payment] = $this->createPlayerPaymentForCredits();

        PlayerCreditMovement::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'type' => PlayerCreditMovement::TYPE_CREDIT,
            'amount' => 20000,
            'movement_date' => now()->toDateString(),
            'concept' => 'Anticipo',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->put("/payments/{$payment->id}", [
                'column' => 'january',
                'january' => (string) Payment::$paid_player_credit,
                'january_amount' => '50000',
            ])
            ->assertUnprocessable()
            ->assertSee('saldo a favor suficiente');

        $this->assertDatabaseMissing('player_credit_movements', [
            'payment_id' => $payment->id,
            'payment_field' => 'january',
            'type' => PlayerCreditMovement::TYPE_DEBIT,
        ]);
    }

    public function test_monthly_payment_with_player_credit_creates_debit_movement(): void
    {
        [$player, $payment] = $this->createPlayerPaymentForCredits();

        PlayerCreditMovement::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'type' => PlayerCreditMovement::TYPE_CREDIT,
            'amount' => 90000,
            'movement_date' => now()->toDateString(),
            'concept' => 'Anticipo',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->put("/payments/{$payment->id}", [
                'column' => 'january',
                'january' => (string) Payment::$paid_player_credit,
                'january_amount' => '50000',
            ])
            ->assertOk()
            ->assertJsonPath('data.january', Payment::$paid_player_credit);

        $this->assertDatabaseHas('player_credit_movements', [
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'payment_id' => $payment->id,
            'payment_field' => 'january',
            'type' => PlayerCreditMovement::TYPE_DEBIT,
            'amount' => 50000,
        ]);
    }

    private function createPlayerPaymentForCredits(): array
    {
        $player = $this->createPlayerForCredits();
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $inscription = Inscription::factory()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);
        $payment = Payment::query()
            ->where('school_id', $this->school['id'])
            ->where('inscription_id', $inscription->id)
            ->where('year', now()->year)
            ->firstOrFail();

        return [$player, $payment];
    }

    private function createPlayerForCredits(array $overrides = []): Player
    {
        return Player::factory()->create($overrides + [
            'school_id' => $this->school['id'],
            'unique_code' => fake()->unique()->numerify('CRD#####'),
        ]);
    }

    private function setPlayerCreditsPermission(School $school, bool $enabled): void
    {
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.player_credits'] = $enabled;
        $school->forceFill(['school_permissions' => School::normalizeSchoolPermissions($permissions)])->save();
        School::forgetCachedSchool($school->id);
    }
}
