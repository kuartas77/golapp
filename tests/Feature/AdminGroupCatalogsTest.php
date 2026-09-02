<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Service\Groups\GroupCatalogCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class AdminGroupCatalogsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_schedule_catalog_endpoints_are_protected_by_training_groups_permission(): void
    {
        $school = School::findOrFail($this->school['id']);

        $this->setSchoolPermissions($school, [
            'school.module.training_groups' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/schedules')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->get('/admin/schedules')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/schedules_enabled?draw=1&start=0&length=10')
            ->assertForbidden();

        $this->setSchoolPermissions($school, [
            'school.module.training_groups' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/schedules')
            ->assertOk();

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/schedules_enabled?draw=1&start=0&length=10')
            ->assertOk();
    }

    public function test_tournament_catalog_endpoints_are_protected_by_competition_groups_permission(): void
    {
        $school = School::findOrFail($this->school['id']);

        $this->setSchoolPermissions($school, [
            'school.module.competition_groups' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/tournaments')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->get('/admin/tournaments')
            ->assertForbidden();

        $this->setSchoolPermissions($school, [
            'school.module.competition_groups' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/tournaments')
            ->assertOk();
    }

    public function test_group_settings_only_expose_school_and_instructor_users(): void
    {
        $instructor = $this->createUser([
            'email' => 'group-instructor@example.com',
            'school_id' => $this->school['id'],
        ], [User::INSTRUCTOR]);
        $assistant = $this->createUser([
            'email' => 'group-assistant@example.com',
            'school_id' => $this->school['id'],
        ], [User::ASSISTANT]);
        $viewer = $this->createUser([
            'email' => 'group-viewer@example.com',
            'school_id' => $this->school['id'],
        ], [User::VIEWER]);

        foreach ([$instructor, $assistant, $viewer] as $user) {
            $this->linkUserToSchool($user, (int) $this->school['id']);
        }

        Cache::forget("KEY_GROUP_ASSIGNABLE_USERS_{$this->school['id']}");

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/settings/groups')
            ->assertOk();

        $userIds = collect($response->json('users'))->pluck('id');

        $this->assertTrue($userIds->contains($this->user->id));
        $this->assertTrue($userIds->contains($instructor->id));
        $this->assertFalse($userIds->contains($assistant->id));
        $this->assertFalse($userIds->contains($viewer->id));
    }

    public function test_group_creation_rejects_users_without_an_assignable_role_or_from_another_school(): void
    {
        $assistant = $this->createUser([
            'email' => 'invalid-group-assistant@example.com',
            'school_id' => $this->school['id'],
        ], [User::ASSISTANT]);
        $this->linkUserToSchool($assistant, (int) $this->school['id']);

        [, $otherInstructor] = $this->createSchoolAndUser([
            'email' => 'other-group-school@example.com',
            'slug' => 'other-group-school',
        ], [User::INSTRUCTOR]);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', [
                'name' => 'Grupo con usuario no permitido',
                'users_id' => [$assistant->id],
                'categories' => [],
                'schedules' => [],
                'days' => ['Lunes'],
                'year_active' => now()->year,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('users_id.0');

        $tournament = Tournament::query()->create([
            'name' => 'Copa usuarios permitidos',
            'school_id' => $this->school['id'],
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/competition_groups', [
                'name' => 'Grupo con instructor externo',
                'user_id' => $otherInstructor->id,
                'tournament_id' => $tournament->id,
                'categories' => ['SUB-9'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('user_id');
    }

    public function test_group_updates_can_preserve_a_historical_assignee(): void
    {
        $assistant = $this->createUser([
            'email' => 'historical-group-assistant@example.com',
            'school_id' => $this->school['id'],
        ], [User::ASSISTANT]);
        $this->linkUserToSchool($assistant, (int) $this->school['id']);

        $trainingGroup = TrainingGroup::query()->create([
            'name' => 'Entrenamiento histórico',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
            'days' => ['Lunes'],
        ]);
        $trainingGroup->instructors()->syncWithPivotValues([$assistant->id], [
            'assigned_year' => now()->year,
        ]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/training_groups/{$trainingGroup->id}", [
                'name' => 'Entrenamiento histórico editado',
                'users_id' => [$assistant->id],
                'categories' => [],
                'schedules' => [],
                'days' => ['Lunes'],
                'year_active' => now()->year,
            ])
            ->assertOk();

        $tournament = Tournament::query()->create([
            'name' => 'Copa histórica',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Competencia histórica',
            'user_id' => $assistant->id,
            'tournament_id' => $tournament->id,
            'categories' => ['SUB-9'],
            'school_id' => $this->school['id'],
        ]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/competition_groups/{$competitionGroup->id}", [
                'name' => 'Competencia histórica editada',
                'user_id' => $assistant->id,
                'tournament_id' => $tournament->id,
                'categories' => ['SUB-9'],
            ])
            ->assertOk();
    }

    public function test_schedule_catalog_crud_is_scoped_to_the_selected_school(): void
    {
        $otherSchool = School::findOrFail($this->createSchool([
            'email' => 'catalog-other-school@example.com',
            'slug' => 'catalog-other-school',
        ])['id']);

        $currentSchedule = Schedule::query()->create([
            'schedule' => '06:00AM - 07:00AM',
            'school_id' => $this->school['id'],
        ]);

        $otherSchedule = Schedule::query()->create([
            'schedule' => '09:00AM - 10:00AM',
            'school_id' => $otherSchool->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/admin/schedules')
            ->assertOk()
            ->assertJsonFragment(['schedule' => '06:00AM - 07:00AM']);

        $scheduleIds = collect($response->json())->pluck('id');

        $this->assertTrue($scheduleIds->contains($currentSchedule->id));
        $this->assertFalse($scheduleIds->contains($otherSchedule->id));

        $createdResponse = $this->actingAs($this->user)
            ->postJson('/api/v2/admin/schedules', [
                'schedule_start' => '07:30am',
                'schedule_end' => '08:30am',
            ])
            ->assertCreated()
            ->assertJsonPath('data.schedule', '07:30AM - 08:30AM');

        $createdId = (int) $createdResponse->json('data.id');

        $this->assertDatabaseHas('schedules', [
            'id' => $createdId,
            'school_id' => $this->school['id'],
            'schedule' => '07:30AM - 08:30AM',
        ]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/schedules/{$currentSchedule->id}", [
                'schedule_start' => '06:15AM',
                'schedule_end' => '07:15AM',
            ])
            ->assertOk()
            ->assertJsonPath('data.schedule', '06:15AM - 07:15AM');

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/admin/schedules/{$currentSchedule->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('schedules', [
            'id' => $currentSchedule->id,
        ]);
    }

    public function test_training_group_accepts_five_days_and_rejects_six_days(): void
    {
        $basePayload = [
            'name' => 'Grupo Cinco Dias',
            'stage' => 'Cancha Norte',
            'users_id' => [$this->user->id],
            'categories' => ['SUB-12'],
            'schedules' => ['07:00AM - 08:00AM'],
            'year_active' => now()->year,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', $basePayload + [
                'days' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('training_groups', [
            'name' => 'Grupo Cinco Dias',
            'school_id' => $this->school['id'],
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', $basePayload + [
                'name' => 'Grupo Seis Dias',
                'days' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('days');
    }

    public function test_training_group_stage_is_optional_when_creating_and_updating(): void
    {
        $payload = [
            'name' => 'Grupo Sin Escenario',
            'users_id' => [$this->user->id],
            'categories' => [],
            'schedules' => ['07:00AM - 08:00AM'],
            'days' => ['Lunes', 'Miércoles'],
            'year_active' => now()->year,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->where('name', 'Grupo Sin Escenario')
            ->firstOrFail();

        $this->assertNull($trainingGroup->stage);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/training_groups/{$trainingGroup->id}", array_merge($payload, [
                'name' => 'Grupo Sin Escenario Editado',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('training_groups', [
            'id' => $trainingGroup->id,
            'name' => 'Grupo Sin Escenario Editado',
            'stage' => null,
        ]);
    }

    public function test_training_group_schedule_is_optional_and_can_be_cleared(): void
    {
        $payload = [
            'name' => 'Grupo Sin Horario',
            'stage' => 'Cancha Norte',
            'users_id' => [$this->user->id],
            'categories' => [],
            'days' => ['Lunes', 'Miércoles'],
            'year_active' => now()->year,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->where('name', 'Grupo Sin Horario')
            ->firstOrFail();

        $this->assertNull($trainingGroup->schedules);
        $this->assertSame(
            'Grupo Sin Horario - Cancha Norte Lunes,Miércoles',
            $trainingGroup->full_schedule_group
        );

        $this->actingAs($this->user)
            ->getJson("/api/v2/admin/training_groups/{$trainingGroup->id}")
            ->assertOk()
            ->assertJsonPath('data.explode_schedules', []);

        $trainingGroup->update(['schedules' => ['07:00AM - 08:00AM']]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/training_groups/{$trainingGroup->id}", [
                ...$payload,
                'schedules' => null,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNull($trainingGroup->fresh()->schedules);
    }

    public function test_provisional_training_group_cannot_be_updated(): void
    {
        $provisionalGroup = School::query()
            ->findOrFail($this->school['id'])
            ->trainingGroups()
            ->where('name', 'Provisional')
            ->firstOrFail();

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/training_groups/{$provisionalGroup->id}", [
                'name' => 'Grupo Modificado',
                'stage' => 'Cancha Norte',
                'users_id' => [$this->user->id],
                'categories' => [],
                'schedules' => [],
                'days' => ['Lunes'],
                'year_active' => now()->year,
                'is_complementary' => false,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->assertSame('Provisional', $provisionalGroup->fresh()->name);
    }

    public function test_training_group_can_be_marked_as_complementary(): void
    {
        $payload = [
            'name' => 'Grupo Complementario Porteros',
            'stage' => 'Cancha Norte',
            'users_id' => [$this->user->id],
            'categories' => [],
            'schedules' => ['07:00AM - 08:00AM'],
            'days' => ['Lunes', 'Miércoles'],
            'year_active' => now()->year,
            'is_complementary' => true,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('training_groups', [
            'name' => 'Grupo Complementario Porteros',
            'school_id' => $this->school['id'],
            'is_complementary' => true,
        ]);

        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->where('name', 'Grupo Complementario Porteros')
            ->firstOrFail();

        $this->actingAs($this->user)
            ->getJson("/api/v2/admin/training_groups/{$trainingGroup->id}")
            ->assertOk()
            ->assertJsonPath('data.is_complementary', true);
    }

    public function test_training_group_tariff_is_ignored_when_disabled_and_required_for_current_groups_when_enabled(): void
    {
        $payload = [
            'name' => 'Grupo con tarifa',
            'stage' => 'Cancha Norte',
            'users_id' => [$this->user->id],
            'categories' => [],
            'schedules' => ['07:00AM - 08:00AM'],
            'days' => ['Lunes'],
            'year_active' => now()->year,
            'is_complementary' => false,
            'monthly_payment_amount' => '$ 85.000',
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', $payload)
            ->assertOk();

        $group = TrainingGroup::query()->where('name', 'Grupo con tarifa')->firstOrFail();
        $this->assertNull($group->monthly_payment_amount);

        $this->actingAs($this->user)
            ->getJson("/api/v2/admin/training_groups/{$group->id}")
            ->assertOk()
            ->assertJsonPath('data.monthly_payment_amount', null);

        School::query()->findOrFail($this->school['id'])
            ->update(['training_group_monthly_payment_enabled' => true]);

        $this->actingAs($this->user)
            ->postJson("/api/v2/admin/training_groups/{$group->id}", [
                ...$payload,
                '_method' => 'PUT',
            ])
            ->assertOk();

        $this->assertSame(85000, $group->fresh()->monthly_payment_amount);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', [
                ...$payload,
                'name' => 'Grupo sin tarifa activo',
                'monthly_payment_amount' => null,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('monthly_payment_amount');

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', [
                ...$payload,
                'name' => 'Grupo histórico sin tarifa',
                'year_active' => now()->year - 1,
                'monthly_payment_amount' => null,
            ])
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', [
                ...$payload,
                'name' => 'Complementario activo',
                'is_complementary' => true,
                'monthly_payment_amount' => 99999,
            ])
            ->assertOk();

        $this->assertDatabaseHas('training_groups', [
            'name' => 'Complementario activo',
            'monthly_payment_amount' => null,
        ]);

        School::query()->findOrFail($this->school['id'])
            ->update(['training_group_monthly_payment_enabled' => false]);

        $this->actingAs($this->user)
            ->postJson("/api/v2/admin/training_groups/{$group->id}", [
                ...$payload,
                '_method' => 'PUT',
                'monthly_payment_amount' => 99000,
            ])
            ->assertOk();

        $this->assertSame(85000, $group->fresh()->monthly_payment_amount);
    }

    public function test_training_group_creation_clears_school_group_cache_keys(): void
    {
        $schoolId = $this->school['id'];
        $userId = $this->user->id;

        Cache::put("KEY_TRAINING_GROUPS_{$schoolId}", 'stale-list');
        Cache::put("KEY_TRAINING_GROUPS_ARR_{$schoolId}", 'stale-array');
        $catalogCache = app(GroupCatalogCache::class);
        $previousVersion = $catalogCache->version($schoolId);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/training_groups', [
                'name' => 'Grupo Limpia Cache',
                'stage' => 'Cancha Norte',
                'users_id' => [$this->user->id],
                'categories' => ['SUB-12'],
                'schedules' => ['07:00AM - 08:00AM'],
                'days' => ['Lunes', 'Miércoles'],
                'year_active' => now()->year,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertFalse(Cache::has("KEY_TRAINING_GROUPS_{$schoolId}"));
        $this->assertFalse(Cache::has("KEY_TRAINING_GROUPS_ARR_{$schoolId}"));
        $this->assertNotSame($previousVersion, $catalogCache->version($schoolId));
    }

    public function test_attendance_classdays_reflect_five_training_days(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 1));

        try {
            $group = TrainingGroup::query()->create([
                'name' => 'Grupo Asistencia Cinco Dias',
                'year' => 2026,
                'category' => ['SUB-12'],
                'days' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                'schedules' => ['07:00AM - 08:00AM'],
                'school_id' => $this->school['id'],
                'year_active' => 2026,
            ]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/v2/training_group/classdays?training_group_id={$group->id}&month=4")
                ->assertOk();

            $this->assertCount(22, $response->json());
            $this->assertSame('assistance_twenty_two', $response->json('21.column'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_tournament_catalog_reactivates_a_soft_deleted_record_in_the_same_school(): void
    {
        $tournament = Tournament::query()->create([
            'name' => 'COPA BARRIAL',
            'school_id' => $this->school['id'],
        ]);

        $tournament->delete();

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/tournaments', [
                'name' => 'copa barrial',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Torneo reactivado correctamente.')
            ->assertJsonPath('data.name', 'COPA BARRIAL');

        $this->assertDatabaseHas('tournaments', [
            'id' => $tournament->id,
            'name' => 'COPA BARRIAL',
            'deleted_at' => null,
        ]);
    }

    private function setSchoolPermissions(School $school, array $overrides): void
    {
        $permissions = array_merge($school->getResolvedSchoolPermissions(), $overrides);

        $school->forceFill([
            'school_permissions' => School::normalizeSchoolPermissions($permissions),
        ])->save();

        School::forgetCachedSchool($school->id);
    }

    private function linkUserToSchool(User $user, int $schoolId): void
    {
        $schoolUser = new SchoolUser;
        $schoolUser->user_id = $user->id;
        $schoolUser->school_id = $schoolId;
        $schoolUser->save();
    }
}
