<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Service\Groups\GroupCatalogCache;
use App\Service\School\CurrentSchoolContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

final class GroupCatalogContextTest extends TestCase
{
    public function test_school_keeps_selected_campus_after_school_model_cache_expires(): void
    {
        $secondary = School::findOrFail($this->createSchool([
            'email' => 'context-campus@example.com',
            'slug' => 'context-campus',
        ])['id']);
        $this->configureCampuses($this->school['id'], [$secondary->id]);

        $this->actingAs($this->user);
        $context = app(CurrentSchoolContext::class);
        $context->select($secondary->id, $this->user);

        Cache::forget(School::cacheKeyFor(School::CACHE_PREFIX_SCHOOL, $secondary->id));

        $this->assertSame($secondary->id, $context->current($this->user)->id);
        $this->assertSame($secondary->id, Session::get(CurrentSchoolContext::SESSION_KEY));
    }

    public function test_unauthorized_school_switch_is_rejected_without_changing_context(): void
    {
        $unrelated = School::findOrFail($this->createSchool([
            'email' => 'context-unrelated@example.com',
            'slug' => 'context-unrelated',
        ])['id']);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/change_school', ['school_id' => $unrelated->id])
            ->assertForbidden();

        $this->assertSame($this->school['id'], app(CurrentSchoolContext::class)->current($this->user)->id);
    }

    public function test_instructor_can_switch_schools_and_receives_only_assigned_groups(): void
    {
        $secondary = School::findOrFail($this->createSchool([
            'email' => 'instructor-campus@example.com',
            'slug' => 'instructor-campus',
        ])['id']);
        $instructor = $this->createUser([
            'email' => 'multi-school-instructor@example.com',
            'school_id' => $this->school['id'],
        ], [User::INSTRUCTOR]);

        foreach ([$this->school['id'], $secondary->id] as $schoolId) {
            SchoolUser::query()->create(['user_id' => $instructor->id, 'school_id' => $schoolId]);
        }

        $campusResponse = $this->actingAs($instructor)
            ->getJson('/api/v2/admin/info_campus')
            ->assertOk();
        $this->assertCount(2, $campusResponse->json('schools'));

        $primaryGroup = $this->assignedTrainingGroup($this->school['id'], $instructor, 'Grupo Escuela A');
        $secondaryGroup = $this->assignedTrainingGroup($secondary->id, $instructor, 'Grupo Escuela B');
        $this->assignedCompetitionGroup($this->school['id'], $instructor, 'Competencia A');
        $secondaryCompetition = $this->assignedCompetitionGroup($secondary->id, $instructor, 'Competencia B');

        $this
            ->postJson('/api/v2/admin/change_school', ['school_id' => $secondary->id])
            ->assertOk();

        $response = $this->getJson('/api/v2/settings/general')->assertOk();
        $trainingIds = collect($response->json('t_groups'))->pluck('id');
        $competitionIds = collect($response->json('competition_groups'))->pluck('id');

        $this->assertSame($secondary->id, $response->json('current_school_id'));
        $this->assertTrue($trainingIds->contains($secondaryGroup->id));
        $this->assertFalse($trainingIds->contains($primaryGroup->id));
        $this->assertTrue($competitionIds->contains($secondaryCompetition->id));
    }

    public function test_group_mutations_rotate_the_school_catalog_version(): void
    {
        $cache = app(GroupCatalogCache::class);
        $schoolId = (int) $this->school['id'];
        $initial = $cache->version($schoolId);

        TrainingGroup::query()->create([
            'name' => 'Grupo Versionado',
            'school_id' => $schoolId,
            'year_active' => now()->year,
            'days' => ['Lunes'],
            'schedules' => ['08:00AM - 09:00AM'],
        ]);
        $afterTraining = $cache->version($schoolId);

        $this->assertNotSame($initial, $afterTraining);

        $group = $this->assignedCompetitionGroup($schoolId, $this->user, 'Competencia Versionada');
        $this->assertNotSame($afterTraining, $cache->version($schoolId));

        $beforeUpdate = $cache->version($schoolId);
        $group->update(['name' => 'Competencia Actualizada']);
        $this->assertNotSame($beforeUpdate, $cache->version($schoolId));
    }

    public function test_super_admin_can_warm_and_revisit_two_school_catalogs_without_crossing_data(): void
    {
        $secondary = School::findOrFail($this->createSchool([
            'email' => 'super-admin-cache-campus@example.com',
            'slug' => 'super-admin-cache-campus',
        ])['id']);
        $primaryGroup = $this->assignedTrainingGroup($this->school['id'], $this->user, 'Catálogo Escuela A');
        $secondaryGroup = $this->assignedTrainingGroup($secondary->id, $this->user, 'Catálogo Escuela B');
        $superAdmin = $this->createUser([
            'email' => 'catalog-super-admin@example.com',
            'school_id' => $this->school['id'],
        ], [User::SUPER_ADMIN]);

        $this->actingAs($superAdmin);

        $this->postJson('/api/v2/admin/change_school', ['school_id' => $this->school['id']])->assertOk();
        $primaryIds = collect($this->getJson('/api/v2/settings/general')->assertOk()->json('t_groups'))->pluck('id');

        $this->postJson('/api/v2/admin/change_school', ['school_id' => $secondary->id])->assertOk();
        $secondaryIds = collect($this->getJson('/api/v2/settings/general')->assertOk()->json('t_groups'))->pluck('id');

        $this->postJson('/api/v2/admin/change_school', ['school_id' => $this->school['id']])->assertOk();
        $revisitedIds = collect($this->getJson('/api/v2/settings/general')->assertOk()->json('t_groups'))->pluck('id');

        $this->assertTrue($primaryIds->contains($primaryGroup->id));
        $this->assertFalse($primaryIds->contains($secondaryGroup->id));
        $this->assertTrue($secondaryIds->contains($secondaryGroup->id));
        $this->assertFalse($secondaryIds->contains($primaryGroup->id));
        $this->assertSame($primaryIds->all(), $revisitedIds->all());
    }

    private function configureCampuses(int $primarySchoolId, array $campusIds): void
    {
        SettingValue::query()->updateOrCreate(
            ['school_id' => $primarySchoolId, 'setting_key' => Setting::MULTIPLE_SCHOOLS],
            ['value' => json_encode($campusIds, JSON_THROW_ON_ERROR)]
        );
        School::forgetCachedSchool($primarySchoolId);
    }

    private function assignedTrainingGroup(int $schoolId, User $instructor, string $name): TrainingGroup
    {
        $group = TrainingGroup::query()->create([
            'name' => $name,
            'school_id' => $schoolId,
            'year_active' => now()->year,
            'days' => ['Lunes'],
            'schedules' => ['08:00AM - 09:00AM'],
        ]);
        $group->instructors()->attach($instructor->id, ['assigned_year' => now()->year]);

        return $group;
    }

    private function assignedCompetitionGroup(int $schoolId, User $instructor, string $name): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'name' => "Torneo {$name}",
            'school_id' => $schoolId,
        ]);

        return CompetitionGroup::query()->create([
            'name' => $name,
            'school_id' => $schoolId,
            'year' => now()->year,
            'category' => 'SUB-15',
            'user_id' => $instructor->id,
            'tournament_id' => $tournament->id,
        ]);
    }
}
