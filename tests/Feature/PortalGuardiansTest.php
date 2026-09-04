<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\SkillsControl;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Modules\Inscriptions\Actions\Create\InviteGuardianAction;
use App\Modules\Inscriptions\Actions\Create\Passable;
use App\Notifications\GuardianPasswordResetNotification;
use App\Service\Player\PlayerExportService;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

final class PortalGuardiansTest extends TestCase
{
    public function test_guardian_can_login_and_load_current_profile(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'guardian@example.com',
            'password' => 'secret-guardian',
        ]);

        $loginResponse = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v2/portal/acudientes/login', [
                'email' => 'guardian@example.com',
                'password' => 'secret-guardian',
            ]);

        $loginResponse->assertOk();
        $this->assertDatabaseHas('peoples', [
            'id' => $guardian->id,
            'email' => 'guardian@example.com',
        ]);
        $this->assertNotNull($guardian->fresh()->last_login_at);

        $meResponse = $this->getJson('/api/v2/portal/acudientes/me');

        $meResponse->assertOk();
        $meResponse->assertJsonPath('email', 'guardian@example.com');
        $meResponse->assertJsonPath('identification_card', $guardian->identification_card);
    }

    public function test_guardian_session_cannot_access_backoffice_api_routes(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'portal-only.guardian@example.com',
            'password' => 'portal-only-secret',
        ]);

        $this->actingAs($guardian, 'guardians')
            ->getJson('/api/v2/settings/general')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Una sesión de backoffice es requerida.');

        $this->actingAs($guardian, 'guardians')
            ->getJson('/api/v2/portal/acudientes/me')
            ->assertOk()
            ->assertJsonPath('email', 'portal-only.guardian@example.com');
    }

    public function test_guardian_can_download_owned_inscription_report_without_role_checks(): void
    {
        [$guardian, $player, $inscription, $school] = $this->createGuardianScenario([
            'email' => 'report.guardian@example.com',
            'password' => 'report-secret',
        ]);

        $export = Mockery::mock(PlayerExportService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $export->shouldReceive('setConfigurationMpdf')->once();
        $export->shouldReceive('createPDF')->once();
        $export->shouldReceive('stream')->once()->andReturn(response('guardian-report'));
        $this->app->instance(PlayerExportService::class, $export);

        $this->actingAs($guardian, 'guardians')
            ->get("/api/v2/portal/acudientes/players/{$player->id}/inscription-report/{$inscription->id}")
            ->assertOk()
            ->assertSeeText('guardian-report');

        $this->assertSame((int) $school->id, (int) $player->school_id);
    }

    public function test_guardian_logout_invalidates_session(): void
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

    public function test_guardian_login_is_blocked_without_current_year_eligible_player(): void
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

    public function test_guardian_login_is_blocked_when_school_tutor_platform_is_disabled(): void
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

    public function test_guardian_only_sees_owned_eligible_players(): void
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

    public function test_guardian_sees_descriptive_attendance_status_instead_of_symbols(): void
    {
        [$guardian, $player, $inscription] = $this->createGuardianScenario([
            'email' => 'attendance.guardian@example.com',
            'password' => 'attendance-secret',
        ]);

        $inscription->trainingGroup()->update(['days' => 'Lunes']);

        $assist = Assist::query()
            ->where('inscription_id', $inscription->id)
            ->where('training_group_id', $inscription->training_group_id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->firstOrFail();
        $assist->update(['assistance_one' => 1]);

        $this->actingAs($guardian, 'guardians')
            ->getJson("/api/v2/portal/acudientes/players/{$player->id}")
            ->assertOk()
            ->assertJsonPath('data.current_inscription.attendance.0.id', $assist->id)
            ->assertJsonPath('data.current_inscription.attendance.0.registers.0.status', 1)
            ->assertJsonPath('data.current_inscription.attendance.0.registers.0.label', 'Asistencia');
    }

    public function test_guardian_player_detail_disables_evaluations_when_school_module_is_disabled(): void
    {
        [$guardian, $player, $inscription] = $this->createGuardianScenario([
            'email' => 'evaluations-disabled.guardian@example.com',
            'password' => 'evaluations-disabled-secret',
        ], schoolAttributes: [
            'school_permissions' => School::normalizeSchoolPermissions([
                'school.module.evaluations' => false,
            ]),
        ]);

        $this->actingAs($guardian, 'guardians')
            ->getJson("/api/v2/portal/acudientes/players/{$player->id}")
            ->assertOk()
            ->assertJsonPath('data.modules.evaluations', false)
            ->assertJsonCount(0, 'data.current_inscription.evaluations')
            ->assertJsonCount(0, 'data.current_inscription.comparison_periods');

        $this->expectException(ModelNotFoundException::class);

        app(GuardianAccessService::class)->findEvaluationEnabledInscription($guardian, $inscription->id);
    }

    public function test_guardian_player_detail_returns_only_meaningful_attendance_and_competition_feedback(): void
    {
        [$guardian, $player, $inscription, $school] = $this->createGuardianScenario([
            'email' => 'feedback.guardian@example.com',
            'password' => 'feedback-secret',
        ]);

        $assist = Assist::query()
            ->where('inscription_id', $inscription->id)
            ->firstOrFail();
        $assist->update([
            'observations' => (object) [
                '2026-03-11' => '  Llegó con buena disposición.  ',
                '2026-03-12' => '   ',
            ],
        ]);

        $tournament = Tournament::query()->create([
            'name' => 'Torneo Apertura',
            'school_id' => $school->id,
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Sub 14',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2012',
            'school_id' => $school->id,
        ]);
        $game = Game::query()->create([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => '2026-03-10',
            'hour' => '08:00 AM',
            'num_match' => '4',
            'place' => 'Cancha principal',
            'rival_name' => 'Academia Norte',
            'final_score' => ['soccer' => 3, 'rival' => 3],
            'general_concept' => '  El grupo sostuvo el plan de juego.  ',
            'status' => Game::STATUS_PLAYED,
            'school_id' => $school->id,
        ]);
        SkillsControl::query()->create([
            'game_id' => $game->id,
            'inscription_id' => $inscription->id,
            'position' => 'Volante (Ofensivo Central)',
            'observation' => '  Mostró buena lectura de juego.  ',
            'school_id' => $school->id,
        ]);

        $response = $this->actingAs($guardian, 'guardians')
            ->getJson("/api/v2/portal/acudientes/players/{$player->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.current_inscription.feedback');

        $feedback = collect($response->json('data.current_inscription.feedback'))->keyBy('source');

        $this->assertSame('Llegó con buena disposición.', $feedback['attendance']['observation']);
        $this->assertSame('2026-03-11', $feedback['attendance']['event_date']);
        $this->assertSame('Mostró buena lectura de juego.', $feedback['competition']['player_observation']);
        $this->assertSame('El grupo sostuvo el plan de juego.', $feedback['competition']['group_observation']);
        $this->assertSame('Volante (Ofensivo Central)', $feedback['competition']['position']);
        $this->assertSame(['team' => 3, 'rival' => 3], $feedback['competition']['score']);
        $this->assertSame('Torneo Apertura', $feedback['competition']['tournament_name']);
    }

    public function test_guardian_sees_complementary_group_attendance_identified_by_group(): void
    {
        [$guardian, $player, $inscription, $school] = $this->createGuardianScenario([
            'email' => 'attendance-complementary.guardian@example.com',
            'password' => 'attendance-complementary-secret',
        ]);

        $inscription->trainingGroup()->update(['name' => 'Principal portal', 'days' => 'Lunes']);
        $complementaryGroup = TrainingGroup::query()->create([
            'name' => 'Porteros portal',
            'school_id' => $school->id,
            'year' => now()->year,
            'year_active' => now()->year,
            'category' => 'Todas las categorías',
            'days' => 'Martes',
            'schedules' => '11:00AM - 12:00PM',
            'is_complementary' => true,
        ]);
        $inscription->update(['complementary_group_id' => $complementaryGroup->id]);

        $inscription->assistance()->withTrashed()->forceDelete();
        Assist::query()->create([
            'school_id' => $school->id,
            'inscription_id' => $inscription->id,
            'training_group_id' => $inscription->training_group_id,
            'year' => now()->year,
            'month' => 1,
            'assistance_one' => 1,
        ]);
        $complementaryAssist = Assist::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $complementaryGroup->id,
            'year' => now()->year,
            'month' => 1,
        ], [
            'school_id' => $school->id,
            'inscription_id' => $inscription->id,
            'training_group_id' => $complementaryGroup->id,
            'year' => now()->year,
            'month' => 1,
            'assistance_one' => 2,
        ]);

        $response = $this->actingAs($guardian, 'guardians')
            ->getJson("/api/v2/portal/acudientes/players/{$player->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data.current_inscription.attendance');

        $attendance = collect($response->json('data.current_inscription.attendance'))->keyBy('id');

        $this->assertSame('Grupo complementario', $attendance[$complementaryAssist->id]['group_label']);
        $this->assertSame('Porteros portal', $attendance[$complementaryAssist->id]['group_name']);
    }

    public function test_guardian_can_update_player_photo(): void
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

    public function test_guardian_cannot_change_access_email_from_profile(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'immutable.guardian@example.com',
            'password' => 'immutable-secret',
        ]);

        $this->actingAs($guardian, 'guardians')
            ->putJson('/api/v2/portal/acudientes/profile', [
                'names' => $guardian->names,
                'email' => 'attacker@example.com',
                'phone' => $guardian->phone,
                'mobile' => $guardian->mobile,
                'profession' => $guardian->profession,
                'business' => $guardian->business,
                'position' => $guardian->position,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertSame('immutable.guardian@example.com', $guardian->fresh()->email);
    }

    public function test_guardian_login_uses_generic_errors_and_is_rate_limited_per_identity(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'limited.guardian@example.com',
            'password' => 'limited-secret',
        ]);

        $invalidPasswordMessage = $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => $guardian->email,
            'password' => 'wrong-secret',
        ])->assertUnprocessable()->json('errors.email.0');

        $missingAccountMessage = $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => 'missing.guardian@example.com',
            'password' => 'wrong-secret',
        ])->assertUnprocessable()->json('errors.email.0');

        $this->assertSame($invalidPasswordMessage, $missingAccountMessage);

        for ($attempt = 2; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v2/portal/acudientes/login', [
                'email' => $guardian->email,
                'password' => 'wrong-secret',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v2/portal/acudientes/login', [
            'email' => $guardian->email,
            'password' => 'wrong-secret',
        ])->assertTooManyRequests();
    }

    public function test_guardian_password_reset_revokes_existing_api_tokens(): void
    {
        [$guardian] = $this->createGuardianScenario([
            'email' => 'reset.guardian@example.com',
            'password' => 'reset-old-secret',
        ]);
        $guardian->createToken('existing-guardian-token');
        $token = Password::broker('guardians')->createToken($guardian);

        $this->postJson('/api/v2/portal/acudientes/reset-password', [
            'token' => $token,
            'email' => $guardian->email,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ])->assertOk();

        $this->assertTrue(Hash::check('NuevaClave123', (string) $guardian->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => People::class,
            'tokenable_id' => $guardian->id,
        ]);
    }

    public function test_backfill_invites_only_unique_eligible_guardians_without_password(): void
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

    public function test_invite_guardian_action_skips_invitation_when_tutor_platform_is_disabled(): void
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

    public function test_invite_guardian_action_sends_invitation_when_tutor_platform_is_enabled(): void
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

    public function test_guardian_access_email_shows_school_name(): void
    {
        [$guardian, , , $school] = $this->createGuardianScenario([
            'email' => 'school-access.guardian@example.com',
        ]);

        $mail = (new GuardianPasswordResetNotification($guardian, 'test-token', true))->toMail($guardian);

        $this->assertSame([config('mail.from.address'), $school->name], $mail->from);
        $this->assertContains("Escuela: {$school->name}", $mail->introLines);
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

        if (! $school->trainingGroups()->exists()) {
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
