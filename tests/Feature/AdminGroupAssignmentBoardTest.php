<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\User;
use Tests\TestCase;

final class AdminGroupAssignmentBoardTest extends TestCase
{
    public function test_training_board_returns_selectors_and_panels_for_school_user(): void
    {
        $this->actingAs($this->user);

        $originGroup = TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->orderBy('id')
            ->firstOrFail();

        $destinationGroup = $this->createTrainingGroup('Avanzado');

        $sourceInscription = $this->createInscription($originGroup, 'Juan', 'Lopez');
        $targetInscription = $this->createInscription($destinationGroup, 'Ana', 'Perez');

        $response = $this->getJson(sprintf(
            '/api/v2/admin/training-groups/board?origin_group_id=%d&target_group_id=%d',
            $originGroup->id,
            $destinationGroup->id
        ))->assertOk();

        $response->assertJsonPath('data.panels.source.count', 1);
        $response->assertJsonPath('data.panels.destination.count', 1);
        $response->assertJsonPath('data.panels.source.items.0.full_names', $sourceInscription->player->full_names);
        $response->assertJsonPath('data.panels.destination.items.0.full_names', $targetInscription->player->full_names);
        $response->assertJsonFragment([
            'value' => (string) $destinationGroup->id,
            'label' => $destinationGroup->full_schedule_group,
        ]);
    }

    public function test_training_move_updates_the_inscription_training_group(): void
    {
        $this->actingAs($this->user);

        $originGroup = TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->orderBy('id')
            ->firstOrFail();

        $destinationGroup = $this->createTrainingGroup('Competitivo');
        $inscription = $this->createInscription($originGroup, 'Mario', 'Suarez');

        $this->postJson('/api/v2/admin/training-groups/move', [
            'inscription_id' => $inscription->id,
            'target_group_id' => $destinationGroup->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame($destinationGroup->id, $inscription->fresh()->training_group_id);
    }

    public function test_training_move_initializes_group_tariff_and_rejects_groups_without_tariff(): void
    {
        $this->actingAs($this->user);

        $school = School::query()->findOrFail($this->school['id']);
        $school->update(['training_group_monthly_payment_enabled' => true]);
        $provisionalGroup = $school->trainingGroups()->where('name', 'Provisional')->firstOrFail();
        $destinationGroup = $this->createTrainingGroup('Tarifa configurada', 88000);
        $groupWithoutTariff = $this->createTrainingGroup('Tarifa pendiente');
        $inscription = $this->createInscription($provisionalGroup, 'Lucia', 'Torres');
        $inscription->forceFill([
            'monthly_payment_type' => Inscription::TRAINING_GROUP_MONTHLY_PAYMENT,
            'monthly_payment_amount' => null,
        ])->save();
        $payment = $inscription->payments()->firstOrFail();
        $monthField = config('variables.KEY_INDEX_MONTHS.'.now()->month);

        $this->postJson('/api/v2/admin/training-groups/move', [
            'inscription_id' => $inscription->id,
            'target_group_id' => $groupWithoutTariff->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('training_group_id');

        $this->assertSame($provisionalGroup->id, $inscription->fresh()->training_group_id);

        $this->postJson('/api/v2/admin/training-groups/move', [
            'inscription_id' => $inscription->id,
            'target_group_id' => $destinationGroup->id,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $inscription->refresh();
        $payment->refresh();

        $this->assertSame($destinationGroup->id, $inscription->training_group_id);
        $this->assertSame(Inscription::TRAINING_GROUP_MONTHLY_PAYMENT, $inscription->monthly_payment_type);
        $this->assertSame(88000, $inscription->monthly_payment_amount);
        $this->assertSame(88000, (int) $payment->{Payment::amountFieldFor($monthField)});
        $this->assertDatabaseHas('payment_change_logs', [
            'payment_id' => $payment->id,
            'field' => $monthField,
            'source' => 'inscription_group_tariff',
            'new_amount' => 88000,
        ]);
    }

    public function test_legacy_training_move_uses_the_same_group_tariff_rules(): void
    {
        $this->actingAs($this->user);

        $school = School::query()->findOrFail($this->school['id']);
        $school->update(['training_group_monthly_payment_enabled' => true]);
        $provisionalGroup = $school->trainingGroups()->where('name', 'Provisional')->firstOrFail();
        $destinationGroup = $this->createTrainingGroup('Tarifa ruta anterior', 91000);
        $inscription = $this->createInscription($provisionalGroup, 'Mateo', 'Rios');
        $inscription->forceFill([
            'monthly_payment_type' => Inscription::TRAINING_GROUP_MONTHLY_PAYMENT,
            'monthly_payment_amount' => null,
        ])->save();

        $this->post(route('ins_training.assign', $inscription->id), [
            'target_group' => $destinationGroup->id,
        ], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonPath('data', true);

        $this->assertSame(91000, $inscription->fresh()->monthly_payment_amount);
    }

    public function test_competition_board_returns_available_pool_and_selected_group_members(): void
    {
        $this->actingAs($this->user);

        $selectedGroup = $this->createCompetitionGroup('Equipo Azul', 'SUB-13');
        $sameYearGroup = $this->createCompetitionGroup('Equipo Blanco', 'SUB-13');
        $otherYearGroup = $this->createCompetitionGroup('Equipo Mayor', 'SUB-15');

        $selectedInscription = $this->createInscription(null, 'David', 'Ruiz');
        $poolInscription = $this->createInscription(null, 'Carlos', 'Mora');
        $poolInscription->update(['category' => 'SUB-18']);

        $selectedGroup->inscriptions()->attach($selectedInscription->id);

        $response = $this->getJson(sprintf(
            '/api/v2/admin/competition-groups/board?competition_group_id=%d',
            $selectedGroup->id
        ))->assertOk();

        $this->assertCount(3, $response->json('data.selectors.groups'));
        $response->assertJsonPath('data.panels.source.count', 1);
        $response->assertJsonPath('data.panels.destination.count', 1);
        $response->assertJsonPath('data.panels.source.items.0.full_names', $poolInscription->player->full_names);
        $response->assertJsonPath('data.panels.source.items.0.unique_code', (string) $poolInscription->player->unique_code);
        $this->assertStringContainsString(
            (string) $poolInscription->player->unique_code,
            $response->json('data.panels.source.items.0.search_text')
        );
        $response->assertJsonPath('data.panels.destination.items.0.full_names', $selectedInscription->player->full_names);
        $response->assertJsonFragment([
            'value' => (string) $sameYearGroup->id,
            'label' => $sameYearGroup->full_name_group,
        ]);
        $response->assertJsonFragment([
            'value' => (string) $otherYearGroup->id,
            'label' => $otherYearGroup->full_name_group,
        ]);
        $response->assertJsonFragment([
            'full_names' => $poolInscription->player->full_names,
        ]);
    }

    public function test_competition_move_assigns_and_detaches_members(): void
    {
        $this->actingAs($this->user);

        $group = $this->createCompetitionGroup('Equipo Naranja', 'SUB-11');
        $inscription = $this->createInscription(null, 'Luis', 'Pardo');

        $this->postJson('/api/v2/admin/competition-groups/move', [
            'inscription_id' => $inscription->id,
            'competition_group_id' => $group->id,
            'assign' => true,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('competition_group_inscription', [
            'competition_group_id' => $group->id,
            'inscription_id' => $inscription->id,
        ]);

        $this->postJson('/api/v2/admin/competition-groups/move', [
            'inscription_id' => $inscription->id,
            'competition_group_id' => $group->id,
            'assign' => false,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('competition_group_inscription', [
            'competition_group_id' => $group->id,
            'inscription_id' => $inscription->id,
        ]);
    }

    public function test_instructor_cannot_access_the_admin_group_assignment_api(): void
    {
        $instructor = $this->createSchoolScopedUser(
            (int) $this->school['id'],
            ['instructor'],
            sprintf('group-assignment-instructor-%s@example.com', uniqid())
        );

        $this->actingAs($instructor)
            ->getJson('/api/v2/admin/training-groups/board')
            ->assertForbidden();
    }

    private function createCompetitionGroup(string $name, string $year): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'name' => sprintf('Torneo %s', $name),
            'school_id' => $this->school['id'],
        ]);

        return CompetitionGroup::query()->create([
            'name' => $name,
            'year' => $year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => $year,
            'school_id' => $this->school['id'],
        ]);
    }

    private function createInscription(?TrainingGroup $trainingGroup, string $names, string $lastNames): Inscription
    {
        $trainingGroup ??= TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->orderBy('id')
            ->firstOrFail();

        $player = Player::factory()->create([
            'names' => $names,
            'last_names' => $lastNames,
            'school_id' => $this->school['id'],
        ]);

        return Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'school_id' => $this->school['id'],
            'category' => 'SUB-13',
        ]);
    }

    private function createSchoolScopedUser(int $schoolId, array $roles, string $email): User
    {
        $user = $this->createUser([
            'school_id' => $schoolId,
            'email' => $email,
        ], $roles);

        SchoolUser::query()->create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);

        return $user;
    }

    private function createTrainingGroup(string $name, ?int $monthlyPaymentAmount = null): TrainingGroup
    {
        $group = TrainingGroup::query()->create([
            'name' => $name,
            'stage' => 'Cancha principal',
            'year' => 'SUB-13',
            'category' => 'SUB-13',
            'days' => 'Lunes,Miércoles',
            'schedules' => '10:00AM - 11:00AM',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
            'monthly_payment_amount' => $monthlyPaymentAmount,
        ]);

        $group->instructors()->attach($this->user->id, [
            'assigned_year' => now()->year,
        ]);

        return $group;
    }
}
