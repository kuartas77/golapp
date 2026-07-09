<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\School;
use App\Models\Tournament;
use App\Models\TrainingGroup;
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
}
