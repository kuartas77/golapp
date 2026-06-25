<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Service\Notification\TopicNotificationStoreService;
use App\Service\Player\PlayerStatsService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class SchoolPermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_new_schools_receive_default_school_permissions(): void
    {
        $school = School::factory()->create();

        $permissions = $school->fresh()->getResolvedSchoolPermissions();
        $catalog = School::permissionCatalog();

        $this->assertTrue($permissions['school.module.players']);
        $this->assertSame(
            (bool) ($catalog['school.module.training_sessions']['default'] ?? false),
            $permissions['school.module.training_sessions']
        );
        $this->assertTrue($permissions['school.module.billing']);
        $this->assertFalse($permissions['school.feature.system_notify']);
    }

    public function test_user_endpoint_returns_selected_school_permissions_and_merged_permissions_for_super_admin(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::findOrFail($this->createSchool([
            'email' => 'secondary-school@example.com',
            'slug' => 'secondary-school',
        ])['id']);

        $this->setSchoolPermissions($secondarySchool, [
            'school.module.players' => false,
            'school.feature.system_notify' => true,
        ]);

        $response = $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/user')
            ->assertOk();

        $payload = $response->json('data');

        $this->assertSame($secondarySchool->id, $payload['school_id']);
        $this->assertSame($secondarySchool->slug, $payload['school_slug']);
        $this->assertFalse($payload['school_permissions']['school.module.players']);
        $this->assertTrue($payload['school_permissions']['school.feature.system_notify']);
        $this->assertContains('school.feature.system_notify', $payload['permissions']);
        $this->assertNotContains('school.module.players', $payload['permissions']);
    }

    public function test_user_endpoint_accepts_selected_school_stored_as_string_in_session(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::findOrFail($this->createSchool([
            'email' => 'string-school@example.com',
            'slug' => 'string-school',
        ])['id']);

        $this->withSession(['admin.selected_school' => (string) $secondarySchool->id])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/user')
            ->assertOk()
            ->assertJsonPath('data.school_id', $secondarySchool->id);
    }

    public function test_user_endpoint_reflects_updated_permissions_for_school_and_instructor_users(): void
    {
        $school = School::findOrFail($this->school['id']);
        $instructor = $this->createSchoolScopedUser(
            $school->id,
            ['instructor'],
            sprintf('instructor-permissions-%s@example.com', uniqid())
        );

        $schoolUserResponse = $this->actingAs($this->user)
            ->getJson('/api/v2/user')
            ->assertOk();

        $this->assertTrue($schoolUserResponse->json('data.school_permissions.school.module.players') ?? $schoolUserResponse->json('data.school_permissions')['school.module.players']);

        $instructorResponse = $this->actingAs($instructor)
            ->getJson('/api/v2/user')
            ->assertOk();

        $this->assertTrue($instructorResponse->json('data.school_permissions.school.module.players') ?? $instructorResponse->json('data.school_permissions')['school.module.players']);

        $this->setSchoolPermissions($school, [
            'school.module.players' => false,
        ]);

        $updatedSchoolUserResponse = $this->actingAs($this->user)
            ->getJson('/api/v2/user')
            ->assertOk();

        $this->assertFalse($updatedSchoolUserResponse->json('data.school_permissions.school.module.players') ?? $updatedSchoolUserResponse->json('data.school_permissions')['school.module.players']);

        $updatedInstructorResponse = $this->actingAs($instructor)
            ->getJson('/api/v2/user')
            ->assertOk();

        $this->assertFalse($updatedInstructorResponse->json('data.school_permissions.school.module.players') ?? $updatedInstructorResponse->json('data.school_permissions')['school.module.players']);
    }

    public function test_school_permission_middleware_blocks_and_allows_admin_users_endpoint(): void
    {
        $school = School::findOrFail($this->school['id']);

        $this->setSchoolPermissions($school, [
            'school.module.user_management' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/users')
            ->assertForbidden();

        $this->setSchoolPermissions($school, [
            'school.module.user_management' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/users')
            ->assertOk();
    }

    public function test_training_sessions_school_permission_blocks_and_allows_module_endpoints(): void
    {
        $school = School::findOrFail($this->school['id']);
        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $school->id)
            ->firstOrFail();
        $trainingSession = $this->createTrainingSession($school, $trainingGroup);

        $this->setSchoolPermissions($school, [
            'school.module.training_sessions' => false,
        ]);

        $this->actingAs($this->user)
            ->get('/training-sessions')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->getJson('/api/v2/datatables/training_sessions_enabled?draw=1&start=0&length=10')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->getJson("/api/v2/training-sessions/{$trainingSession->id}")
            ->assertForbidden();

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/training-sessions/{$trainingSession->id}")
            ->assertForbidden();

        $this->actingAs($this->user)
            ->get(route('export.training_sessions.pdf', ['id' => $trainingSession->id]))
            ->assertForbidden();

        $this->setSchoolPermissions($school, [
            'school.module.training_sessions' => true,
        ]);

        $this->actingAs($this->user)
            ->get('/training-sessions')
            ->assertOk();

        $this->actingAs($this->user)
            ->getJson('/api/v2/datatables/training_sessions_enabled?draw=1&start=0&length=10')
            ->assertOk();

        $this->actingAs($this->user)
            ->getJson("/api/v2/training-sessions/{$trainingSession->id}")
            ->assertOk();

        $this->actingAs($this->user)
            ->get(route('export.training_sessions.pdf', ['id' => $trainingSession->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/training-sessions/{$trainingSession->id}")
            ->assertOk();

        $this->assertSoftDeleted('training_sessions', [
            'id' => $trainingSession->id,
        ]);
    }

    public function test_info_campus_endpoint_is_available_without_school_profile_permission_and_for_instructors(): void
    {
        $school = School::findOrFail($this->school['id']);

        $this->setSchoolPermissions($school, [
            'school.module.school_profile' => false,
        ]);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/admin/info_campus')
            ->assertOk()
            ->assertJsonPath('is_school', true)
            ->assertJsonPath('school_selected', $school->name);

        $instructor = $this->createSchoolScopedUser($school->id, ['instructor'], sprintf('instructor-campus-%s@example.com', uniqid()));

        $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/admin/info_campus')
            ->assertOk()
            ->assertJsonPath('is_school', true)
            ->assertJsonPath('school_selected', $school->name);
    }

    public function test_instructor_can_access_kpis_but_still_cannot_access_players_or_inscriptions_even_when_school_permissions_are_enabled(): void
    {
        $school = School::findOrFail($this->school['id']);
        $instructor = $this->createSchoolScopedUser(
            $school->id,
            ['instructor'],
            sprintf('instructor-restricted-%s@example.com', uniqid())
        );

        [$player, $inscription] = $this->createPlayerWithInscription($school);

        $this->setSchoolPermissions($school, [
            'school.module.players' => true,
            'school.module.inscriptions' => true,
        ]);

        $this->mock(PlayerStatsService::class, function (MockInterface $mock) use ($school, $player): void {
            $mock->shouldReceive('getRankingPayload')
                ->once()
                ->with($school->id, $school->name, [])
                ->andReturn(['players' => [['player_id' => $player->id]]]);

            $mock->shouldReceive('getTopPlayersPayload')
                ->once()
                ->with($school->id)
                ->andReturn(['top_scorers' => [['player_id' => $player->id]]]);

            $mock->shouldReceive('getPlayerDetailPayload')
                ->once()
                ->with($player->id, $school->id)
                ->andReturn(['player' => ['player_id' => $player->id]]);
        });

        $this->actingAs($instructor)
            ->getJson('/api/v2/kpis')
            ->assertOk();

        $this->actingAs($instructor)
            ->getJson('/api/v2/player-stats')
            ->assertOk()
            ->assertJsonPath('players.0.player_id', $player->id);

        $this->actingAs($instructor)
            ->getJson('/api/v2/top-players')
            ->assertOk()
            ->assertJsonPath('top_scorers.0.player_id', $player->id);

        $this->actingAs($instructor)
            ->getJson("/api/v2/player/{$player->id}/detail")
            ->assertOk()
            ->assertJsonPath('player.player_id', $player->id);

        $this->actingAs($instructor)
            ->getJson("/api/v2/players/{$player->unique_code}")
            ->assertForbidden();

        $this->actingAs($instructor)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/edit")
            ->assertForbidden();

        $this->actingAs($instructor)
            ->getJson('/api/v2/datatables/players_enabled?draw=1&start=0&length=10')
            ->assertForbidden();

        $this->actingAs($instructor)
            ->getJson('/api/v2/datatables/inscriptions_enabled?draw=1&start=0&length=10')
            ->assertForbidden();

        $this->actingAs($instructor)
            ->get('/players')
            ->assertForbidden();

        $this->actingAs($instructor)
            ->get('/inscriptions')
            ->assertForbidden();
    }

    public function test_super_admin_can_fetch_and_update_school_permissions(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $school = School::findOrFail($this->school['id']);

        $fetchResponse = $this->withSession(['admin.selected_school' => $school->id])
            ->actingAs($superAdmin)
            ->getJson("/api/v2/admin/schools/{$school->slug}/permissions")
            ->assertOk();

        $this->assertSame($school->slug, $fetchResponse->json('school.slug'));

        $permissions = $fetchResponse->json('permissions');
        $permissions['school.module.players'] = false;

        $updateResponse = $this->withSession(['admin.selected_school' => $school->id])
            ->actingAs($superAdmin)
            ->putJson("/api/v2/admin/schools/{$school->slug}/permissions", [
                'permissions' => $permissions,
            ])
            ->assertOk();

        $updatedPermissions = $updateResponse->json('permissions');

        $this->assertFalse($updatedPermissions['school.module.players']);

        $this->assertFalse($school->fresh()->hasSchoolPermission('school.module.players'));
    }

    public function test_system_notify_school_permission_blocks_and_allows_notification_options(): void
    {
        $school = School::findOrFail($this->school['id']);

        $this->setSchoolPermissions($school, [
            'school.feature.system_notify' => false,
        ]);

        $this->actingAs($this->user)
            ->get('/notifications/options')
            ->assertForbidden();

        $this->setSchoolPermissions($school, [
            'school.feature.system_notify' => true,
        ]);

        $this->actingAs($this->user)
            ->get('/notifications/options')
            ->assertOk();
    }

    public function test_create_invoices_command_only_processes_schools_with_system_notify_enabled(): void
    {
        $enabledSchool = School::findOrFail($this->school['id']);
        $disabledSchool = School::findOrFail($this->createSchool([
            'email' => 'disabled-school@example.com',
            'slug' => 'disabled-school',
        ])['id']);

        $enabledSchool->forceFill(['auto_invoice' => true])->save();
        $disabledSchool->forceFill(['auto_invoice' => true])->save();

        $this->setSchoolPermissions($enabledSchool, [
            'school.feature.system_notify' => true,
        ]);
        $this->setSchoolPermissions($disabledSchool, [
            'school.feature.system_notify' => false,
        ]);

        [$enabledPlayer, $enabledInscription] = $this->createPlayerWithInscription($enabledSchool);
        $this->createPlayerWithInscription($disabledSchool);

        $this->mock(InvoiceRepository::class, function (MockInterface $mock) use ($enabledSchool, $enabledInscription) {
            $mock->shouldReceive('makeInvoice')
                ->once()
                ->with(
                    $enabledInscription->id,
                    Mockery::on(fn (School $school) => $school->id === $enabledSchool->id)
                )
                ->andReturn([
                    $enabledInscription->loadMissing('player'),
                    [[
                        'month' => 'january',
                        'name' => 'Enero',
                        'amount' => 50000,
                        'payment_id' => 1,
                    ]],
                ]);

            $mock->shouldReceive('addUniformRequest')
                ->once()
                ->with($enabledInscription->player_id, $enabledSchool->id)
                ->andReturn([]);

            $mock->shouldReceive('storeInvoice')
                ->once()
                ->with(Mockery::on(function (array $payload) use ($enabledSchool, $enabledInscription) {
                    return $payload['school_id'] === $enabledSchool->id
                        && $payload['inscription_id'] === $enabledInscription->id
                        && count($payload['items']) === 1;
                }))
                ->andReturn([
                    'id' => 123,
                    'created' => true,
                ]);

            $mock->shouldReceive('buildAutoInvoiceIdempotencyKey')
                ->once()
                ->andReturn('test-auto-invoice-key');
        });

        $this->mock(TopicNotificationStoreService::class, function (MockInterface $mock) use ($enabledSchool, $enabledPlayer) {
            $mock->shouldReceive('saveNotification')
                ->once()
                ->with(
                    Mockery::on(fn (array $payload) => $payload['school_id'] === $enabledSchool->id),
                    Mockery::type('array'),
                    Mockery::on(fn (array $playerIds) => in_array($enabledPlayer->id, $playerIds, true))
                );
        });

        $this->artisan('create:invoices')->assertExitCode(0);
    }

    private function setSchoolPermissions(School $school, array $overrides): void
    {
        $permissions = array_merge($school->getResolvedSchoolPermissions(), $overrides);

        $school->forceFill([
            'school_permissions' => School::normalizeSchoolPermissions($permissions),
        ])->save();

        School::forgetCachedSchool($school->id);
    }

    private function createSuperAdminForSchool(int $schoolId): User
    {
        return $this->createSchoolScopedUser(
            $schoolId,
            ['super-admin'],
            sprintf('superadmin-%s@example.com', uniqid())
        );
    }

    private function createSchoolScopedUser(int $schoolId, array $roles, string $email): User
    {
        $user = $this->createUser([
            'email' => $email,
            'school_id' => $schoolId,
        ], $roles);

        SchoolUser::query()->create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);

        return $user;
    }

    private function createPlayerWithInscription(School $school): array
    {
        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $school->id)
            ->firstOrFail();

        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => strtoupper(substr($school->slug, 0, 6)).random_int(100, 999),
        ]);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'school_id' => $school->id,
            'year' => now()->year,
        ]);

        return [$player, $inscription];
    }

    private function createTrainingSession(School $school, TrainingGroup $trainingGroup): TrainingSession
    {
        return TrainingSession::query()->create([
            'school_id' => $school->id,
            'user_id' => $this->user->id,
            'training_group_id' => $trainingGroup->id,
            'year' => now()->year,
            'period' => '1',
            'session' => '1',
            'date' => now()->format('Y-m-d'),
            'hour' => '02:00 PM',
            'training_ground' => 'Cancha principal',
            'material' => 'Conos',
            'warm_up' => 'Activación',
            'back_to_calm' => '5',
            'players' => '18',
            'absences' => 'Sin ausencias',
            'incidents' => 'Sin incidentes',
            'feedback' => 'Sesión positiva',
        ]);
    }
}
