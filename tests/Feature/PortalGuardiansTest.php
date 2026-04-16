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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

    public function testGuardianLogoutInvalidatesSession(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'logout.guardian@example.com',
            'password' => 'logout-secret',
        ]);

        $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => $guardian->email,
            'password' => 'logout-secret',
        ])->assertOk();

        $this->postJson('/api/v2/portal/acudientes/logout')
            ->assertOk();

        $this->getJson('/api/v2/portal/acudientes/me')
            ->assertUnauthorized();
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

    public function testGuardianLoginIsBlockedWhenSchoolTutorPlatformIsDisabled(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'blocked-platform.guardian@example.com',
            'password' => 'blocked-platform-secret',
        ], schoolAttributes: ['tutor_platform' => false]);

        $response = $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => $guardian->email,
            'password' => 'blocked-platform-secret',
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

    public function testGuardianCanUpdatePlayerPhoto(): void
    {
        Storage::fake('public');

        [$guardian, $player] = $this->createGuardianScenario([
            'email' => 'photo.guardian@example.com',
            'password' => 'photo-secret',
        ]);

        $this->actingAs($guardian, 'guardians');

        $response = $this->withHeader('Accept', 'application/json')->post(
            "/api/v2/portal/acudientes/players/{$player->id}",
            [
                '_method' => 'PUT',
                'photo' => UploadedFile::fake()->image('guardian-photo.jpg'),
                'names' => 'Jugador',
                'last_names' => 'Actualizado',
                'date_birth' => '2013-05-11',
                'place_birth' => 'Medellin',
                'document_type' => 'Tarjeta de Indentidad',
                'gender' => 'M',
                'email' => 'jugador.actualizado@example.com',
                'mobile' => '3001234567',
                'phones' => '6041234567',
                'medical_history' => 'Sin novedades',
                'school' => 'Colegio Demo',
                'degree' => '7',
                'jornada' => 'Mañana',
                'address' => 'Calle 10 # 20 - 30',
                'municipality' => 'Medellin',
                'neighborhood' => 'Laureles',
                'rh' => 'O+',
                'eps' => 'Sura',
                'student_insurance' => 'Seguro escolar',
            ]
        );

        $response->assertOk();

        $savedPhotoPath = $player->fresh()->getRawOriginal('photo');

        $this->assertNotEmpty($savedPhotoPath);
        Storage::disk('public')->assertExists($savedPhotoPath);
        $response->assertJsonPath('data.id', $player->id);
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

    public function testInviteGuardianActionSkipsInvitationWhenTutorPlatformIsDisabled(): void
    {
        Notification::fake();

        [$guardian, , , $school] = $this->createGuardianScenario([
            'email' => 'no-platform.guardian@example.com',
        ]);
        $school->setAttribute('tutor_platform', false);

        $passable = new Passable(['school_data' => $school]);
        $passable->setSchool();
        $passable->setGuardian($guardian);
        $passable->setShouldInviteGuardian(true);

        app(InviteGuardianAction::class)->handle($passable, fn (Passable $value) => $value);

        Notification::assertNotSentTo($guardian, GuardianPasswordResetNotification::class);
        $this->assertNull($guardian->fresh()->invited_at);
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
            ? tap(School::query()->findOrFail($this->school['id']))->update(['tutor_platform' => true])
            : School::factory()->create(array_merge([
                'email' => fake()->unique()->safeEmail(),
                'tutor_platform' => true,
            ], $schoolAttributes));

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
