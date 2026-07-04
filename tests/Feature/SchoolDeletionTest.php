<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\DeleteSchoolPermanently;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Service\SchoolDeletionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\WithLogin;

class SchoolDeletionTest extends TestCase
{
    use LazilyRefreshDatabase;
    use WithLogin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSettings();
    }

    public function test_super_admin_can_schedule_a_confirmed_school_deletion(): void
    {
        Queue::fake();
        [$school] = $this->createSchoolAndUser();
        $superAdmin = $this->createUser(roles: [User::SUPER_ADMIN]);

        $this->actingAs($superAdmin)
            ->deleteJson("/api/v2/admin/schools/{$school['slug']}", ['confirmation' => $school['name']])
            ->assertAccepted();

        $this->assertDatabaseHas('schools', [
            'id' => $school['id'],
            'is_enable' => false,
            'deletion_status' => 'pending',
        ]);
        Queue::assertPushed(DeleteSchoolPermanently::class, fn ($job) => $job->schoolId === $school['id']);
    }

    public function test_confirmation_must_match_the_school_name(): void
    {
        Queue::fake();
        [$school] = $this->createSchoolAndUser();
        $superAdmin = $this->createUser(roles: [User::SUPER_ADMIN]);

        $this->actingAs($superAdmin)
            ->deleteJson("/api/v2/admin/schools/{$school['slug']}", ['confirmation' => 'otra escuela'])
            ->assertUnprocessable();

        Queue::assertNothingPushed();
    }

    public function test_deletion_removes_school_data_but_preserves_a_shared_user(): void
    {
        [$school, $sharedUser] = $this->createSchoolAndUser();
        $otherSchool = School::factory()->create();
        SchoolUser::query()->create(['school_id' => $otherSchool->id, 'user_id' => $sharedUser->id]);

        app(SchoolDeletionService::class)->delete($school['id']);

        $this->assertDatabaseMissing('schools', ['id' => $school['id']]);
        $this->assertDatabaseMissing('training_groups', ['school_id' => $school['id']]);
        $this->assertDatabaseHas('users', ['id' => $sharedUser->id, 'school_id' => $otherSchool->id]);
        $this->assertDatabaseHas('schools_user', ['school_id' => $otherSchool->id, 'user_id' => $sharedUser->id]);
    }
}
