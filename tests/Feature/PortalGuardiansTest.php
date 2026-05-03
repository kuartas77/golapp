<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Modules\Inscriptions\Actions\Create\InviteGuardianAction;
use App\Modules\Inscriptions\Actions\Create\Passable;
use App\Notifications\GuardianPasswordResetNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class PortalGuardiansTest extends TestCase
{
    public function testGuardianCanLoginAndSeeOwnedPlayersDashboard(): void
    {
        [$guardian, $player] = $this->createGuardianScenario([
            'email' => 'guardian@example.com',
            'password' => 'Secret123',
        ]);

        $response = $this->post(route('portal.guardian.login'), [
            'email' => 'guardian@example.com',
            'password' => 'Secret123',
        ]);

        $response->assertRedirect(route('portal.guardians.home'));
        $this->assertNotNull($guardian->fresh()->last_login_at);

        $dashboardResponse = $this->get(route('portal.guardians.home'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee($player->full_names);
        $dashboardResponse->assertSee($player->unique_code);
    }

    public function testGuardianCanOnlySeeOwnedPlayers(): void
    {
        [$guardian, $ownedPlayer] = $this->createGuardianScenario([
            'email' => 'owner.guardian@example.com',
            'password' => 'Owner1234',
        ]);

        [, $otherPlayer] = $this->createGuardianScenario([
            'email' => 'other.guardian@example.com',
            'password' => 'Other1234',
        ]);

        $this->actingAs($guardian, 'guardians');

        $this->get(route('portal.guardians.players.show', [$ownedPlayer]))
            ->assertOk()
            ->assertSee($ownedPlayer->full_names);

        $this->get(route('portal.guardians.players.show', [$otherPlayer]))
            ->assertNotFound();
    }

    public function testGuardianLoginIsBlockedWithoutCurrentYearEligiblePlayer(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'blocked.guardian@example.com',
            'password' => 'Blocked123',
        ], inscriptionYear: now()->year - 1);

        $response = $this->from(route('portal.login.form'))->post(route('portal.guardian.login'), [
            'email' => $guardian->email,
            'password' => 'Blocked123',
        ]);

        $response->assertRedirect(route('portal.login.form'));
        $response->assertSessionHasErrors(['email']);
    }

    public function testBackfillInvitesOnlyUniqueEligibleGuardiansWithoutPassword(): void
    {
        Notification::fake();

        [$invitableGuardian] = $this->createGuardianScenario([
            'email' => 'invite.guardian@example.com',
        ]);

        [$configuredGuardian] = $this->createGuardianScenario([
            'email' => 'configured.guardian@example.com',
            'password' => 'Configured123',
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

        [$disabledPlatformGuardian] = $this->createGuardianScenario([
            'email' => 'disabled-platform.guardian@example.com',
        ], schoolAttributes: ['tutor_platform' => false]);

        $this->artisan('portal:guardians-backfill', ['--send' => true])
            ->assertExitCode(0);

        Notification::assertSentTo($invitableGuardian, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($configuredGuardian, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($duplicateGuardianA, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($duplicateGuardianB, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($inactiveSchoolGuardian, GuardianPasswordResetNotification::class);
        Notification::assertNotSentTo($disabledPlatformGuardian, GuardianPasswordResetNotification::class);

        $this->assertNotNull($invitableGuardian->fresh()->invited_at);
        $this->assertNull($configuredGuardian->fresh()->invited_at);
        $this->assertNull($duplicateGuardianA->fresh()->invited_at);
        $this->assertNull($duplicateGuardianB->fresh()->invited_at);
        $this->assertNull($inactiveSchoolGuardian->fresh()->invited_at);
        $this->assertNull($disabledPlatformGuardian->fresh()->invited_at);
    }

    public function testInviteGuardianActionSendsInvitationWhenTutorPlatformIsEnabled(): void
    {
        Notification::fake();

        [$guardian, , , $school] = $this->createGuardianScenario([
            'email' => 'platform.guardian@example.com',
        ]);
        $school->setAttribute('tutor_platform', true);

        $passable = new Passable(['school_data' => $school]);
        $passable->setSchool();
        $passable->setGuardian($guardian);
        $passable->setShouldInviteGuardian(true);

        app(InviteGuardianAction::class)->handle($passable, fn (Passable $value) => $value);

        Notification::assertSentTo($guardian, GuardianPasswordResetNotification::class);
        $this->assertNotNull($guardian->fresh()->invited_at);
    }

    private function createGuardianScenario(
        array $guardianAttributes = [],
        ?int $inscriptionYear = null,
        array $schoolAttributes = []
    ): array {
        $school = empty($schoolAttributes)
            ? School::query()->findOrFail($this->school['id'])
            : School::factory()->create(array_merge([
                'email' => fake()->unique()->safeEmail(),
                'tutor_platform' => true,
                'is_enable' => true,
            ], $schoolAttributes));

        if (empty($schoolAttributes)) {
            $school->update([
                'tutor_platform' => true,
                'is_enable' => true,
            ]);
        }

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
