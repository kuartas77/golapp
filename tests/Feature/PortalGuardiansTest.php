<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Notifications\GuardianPasswordResetNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class PortalGuardiansTest extends TestCase
{
    public function testGuardianCanLoginAndLoadCurrentProfile(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'guardian@example.com',
            'password' => 'secret-guardian',
        ]);

        $loginResponse = $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => 'guardian@example.com',
            'password' => 'secret-guardian',
        ]);

        $loginResponse->assertOk();
        $this->assertDatabaseHas('peoples', [
            'id' => $guardian->id,
            'email' => 'guardian@example.com',
        ]);
        $this->assertNotNull($guardian->fresh()->last_login_at);

        $this->actingAs($guardian, 'guardians');
        $meResponse = $this->getJson('/api/v2/portal/acudientes/me');

        $meResponse->assertOk();
        $meResponse->assertJsonPath('email', 'guardian@example.com');
        $meResponse->assertJsonPath('identification_card', $guardian->identification_card);
    }

    public function testGuardianLoginIsBlockedWithoutCurrentYearEligiblePlayer(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'blocked.guardian@example.com',
            'password' => 'blocked-secret',
        ], inscriptionYear: now()->year - 1);

        $response = $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => $guardian->email,
            'password' => 'blocked-secret',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function testGuardianOnlySeesOwnedEligiblePlayers(): void
    {
        [$guardian, $ownedPlayer] = $this->createGuardianScenario([
            'email' => 'owner.guardian@example.com',
            'password' => 'owner-secret',
        ]);

        [, $otherPlayer] = $this->createGuardianScenario([
            'email' => 'other.guardian@example.com',
            'password' => 'other-secret',
        ]);

        $this->actingAs($guardian, 'guardians');

        $indexResponse = $this->getJson('/api/v2/portal/acudientes/players');
        $indexResponse->assertOk();
        $indexResponse->assertJsonCount(1, 'data');
        $indexResponse->assertJsonPath('data.0.id', $ownedPlayer->id);

        $showResponse = $this->getJson("/api/v2/portal/acudientes/players/{$otherPlayer->id}");
        $showResponse->assertNotFound();
    }

    public function testBackfillInvitesOnlyUniqueEligibleGuardiansWithoutPassword(): void
    {
        Notification::fake();

        [$invitableGuardian] = $this->createGuardianScenario([
            'email' => 'invite.guardian@example.com',
        ]);

        [$configuredGuardian] = $this->createGuardianScenario([
            'email' => 'configured.guardian@example.com',
            'password' => 'already-configured',
        ]);

        [$duplicateGuardianA] = $this->createGuardianScenario([
            'email' => 'duplicate.guardian@example.com',
        ]);
        [$duplicateGuardianB] = $this->createGuardianScenario([
            'email' => 'duplicate.guardian@example.com',
        ]);

        [$inactiveSchoolGuardian] = $this->createGuardianScenario([
            'email' => 'inactive-school.guardian@example.com',
        ], schoolAttributes: ['is_enable' => false]);

        $this->artisan('portal:guardians-backfill', ['--send' => true])
            ->assertExitCode(0);

        Notification::assertSentTo($invitableGuardian, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($configuredGuardian, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($duplicateGuardianA, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($duplicateGuardianB, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($inactiveSchoolGuardian, GuardianPasswordResetNotification::class);

        $this->assertNotNull($invitableGuardian->fresh()->invited_at);
        $this->assertNull($configuredGuardian->fresh()->invited_at);
        $this->assertNull($duplicateGuardianA->fresh()->invited_at);
        $this->assertNull($duplicateGuardianB->fresh()->invited_at);
        $this->assertNull($inactiveSchoolGuardian->fresh()->invited_at);
    }

    private function createGuardianScenario(
        array $guardianAttributes = [],
        ?int $inscriptionYear = null,
        array $schoolAttributes = []
    ): array {
        $school = empty($schoolAttributes)
            ? School::query()->findOrFail($this->school['id'])
            : School::factory()->create($schoolAttributes + ['email' => fake()->unique()->safeEmail()]);

        if (!$school->trainingGroups()->exists()) {
            $school->schedules()->create([
                'schedule' => '10:00AM - 11:00AM',
            ]);

            $school->trainingGroups()->create([
                'name' => 'Provisional',
                'year' => now()->year,
                'category' => 'Todas las categorías',
                'days' => 'Grupo predeterminado',
                'schedules' => '10:00AM - 11:00AM',
            ]);
        }

        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => (string) fake()->unique()->numerify('##########'),
            'identification_document' => (string) fake()->unique()->numerify('##########'),
        ]);

        $guardian = People::factory()->create(array_merge([
            'tutor' => true,
            'email' => fake()->unique()->safeEmail(),
            'identification_card' => (string) fake()->unique()->numerify('##########'),
        ], $guardianAttributes));

        $player->people()->attach($guardian->id);

        $trainingGroupId = TrainingGroup::query()
            ->where('school_id', $school->id)
            ->value('id');

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $inscriptionYear ?? now()->year,
            'school_id' => $school->id,
            'training_group_id' => $trainingGroupId,
            'competition_group_id' => null,
            'category' => categoriesName((int) date('Y', strtotime($player->date_birth))),
        ]);

        return [$guardian, $player, $inscription, $school];
    }
}
