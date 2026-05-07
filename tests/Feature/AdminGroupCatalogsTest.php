<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\School;
use App\Models\Tournament;
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
