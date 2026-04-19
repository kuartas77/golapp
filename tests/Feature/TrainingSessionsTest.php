<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\TrainingSessionDetail;
use App\Models\User;
use Tests\TestCase;

final class TrainingSessionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function testSchoolUserCanListShowStoreUpdateAndExportTrainingSessions(): void
    {
        $group = $this->createTrainingGroup($this->school['id'], $this->user);
        $existingSession = $this->createTrainingSession($group, $this->user);

        $listResponse = $this->actingAs($this->user)
            ->getJson('/api/v2/datatables/training_sessions_enabled?draw=1&start=0&length=10')
            ->assertOk();

        $this->assertContains($existingSession->id, collect($listResponse->json('data'))->pluck('id')->all());
        $this->assertSame(
            $this->user->name,
            html_entity_decode((string) $listResponse->json('data.0.creator_name'), ENT_QUOTES | ENT_HTML5)
        );
        $this->assertSame(3, $listResponse->json('data.0.tasks_count'));
        $this->assertNotEmpty($listResponse->json('data.0.training_group_name'));

        $this->actingAs($this->user)
            ->getJson("/api/v2/training-sessions/{$existingSession->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $existingSession->id)
            ->assertJsonCount(3, 'data.tasks')
            ->assertJsonPath('data.tasks.0.task_name', 'EX1')
            ->assertJsonPath('data.tasks.1.task_name', 'EX2')
            ->assertJsonPath('data.tasks.2.task_name', 'EX3');

        $storeResponse = $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id))
            ->assertCreated()
            ->assertJsonPath('data.training_group_id', $group->id);

        $createdId = (int) $storeResponse->json('data.id');

        $this->assertDatabaseHas('training_sessions', [
            'id' => $createdId,
            'school_id' => $this->school['id'],
            'user_id' => $this->user->id,
            'training_group_id' => $group->id,
            'period' => '1',
            'session' => '1',
        ]);

        $this->assertSame(
            [1, 2, 3],
            TrainingSessionDetail::query()
                ->where('training_session_id', $createdId)
                ->orderBy('task_number')
                ->pluck('task_number')
                ->all()
        );

        $this->actingAs($this->user)
            ->putJson(
                "/api/v2/training-sessions/{$createdId}",
                $this->sessionPayload($group->id, [
                    'period' => '2',
                    'session' => '4',
                    'training_ground' => 'Cancha alterna',
                    'tasks' => $this->makeTasks('UP'),
                ])
            )
            ->assertOk()
            ->assertJsonPath('data.period', '2');

        $this->assertDatabaseHas('training_sessions', [
            'id' => $createdId,
            'user_id' => $this->user->id,
            'period' => '2',
            'session' => '4',
            'training_ground' => 'Cancha alterna',
        ]);

        $this->assertSame(
            ['UP1', 'UP2', 'UP3'],
            TrainingSessionDetail::query()
                ->where('training_session_id', $createdId)
                ->orderBy('task_number')
                ->pluck('task_name')
                ->all()
        );

        $this->actingAs($this->user)
            ->get(route('export.training_sessions.pdf', ['id' => $createdId]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function testSuperAdminCanManageTrainingSessionsForSelectedSchool(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::findOrFail($this->createSchool([
            'email' => 'training-sessions-secondary@example.com',
            'slug' => 'training-sessions-secondary',
        ])['id']);

        $group = $this->createTrainingGroup($secondarySchool->id, $superAdmin, now()->year, 'Elite');
        $existingSession = $this->createTrainingSession($group, $superAdmin, now()->year, 'AD');

        $listResponse = $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/datatables/training_sessions_enabled?draw=1&start=0&length=10')
            ->assertOk();

        $this->assertContains($existingSession->id, collect($listResponse->json('data'))->pluck('id')->all());

        $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->getJson("/api/v2/training-sessions/{$existingSession->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $existingSession->id);

        $storeResponse = $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'training_ground' => 'Sede norte',
            ]))
            ->assertCreated();

        $createdId = (int) $storeResponse->json('data.id');

        $this->assertDatabaseHas('training_sessions', [
            'id' => $createdId,
            'school_id' => $secondarySchool->id,
            'training_group_id' => $group->id,
        ]);

        $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->putJson("/api/v2/training-sessions/{$createdId}", $this->sessionPayload($group->id, [
                'period' => '8',
                'tasks' => $this->makeTasks('SA'),
            ]))
            ->assertOk()
            ->assertJsonPath('data.period', '8');

        $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->get(route('export.training_sessions.pdf', ['id' => $createdId]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function testInstructorOnlyAccessesAssignedTrainingGroupSessions(): void
    {
        $instructor = $this->createSchoolUserWithRole($this->school['id'], ['instructor'], 'coach-training-sessions@example.com');
        $allowedGroup = $this->createTrainingGroup($this->school['id'], $instructor, now()->year, 'Permitido');
        $blockedGroup = $this->createTrainingGroup($this->school['id'], null, now()->year, 'Bloqueado');

        $allowedSession = $this->createTrainingSession($allowedGroup, $this->user, now()->year, 'IN');
        $blockedSession = $this->createTrainingSession($blockedGroup, $this->user, now()->year, 'NO');

        $listResponse = $this->actingAs($instructor)
            ->getJson('/api/v2/datatables/training_sessions_enabled?draw=1&start=0&length=10')
            ->assertOk();

        $listedIds = collect($listResponse->json('data'))->pluck('id')->all();

        $this->assertContains($allowedSession->id, $listedIds);
        $this->assertNotContains($blockedSession->id, $listedIds);

        $this->actingAs($instructor)
            ->getJson("/api/v2/training-sessions/{$allowedSession->id}")
            ->assertOk();

        $this->actingAs($instructor)
            ->getJson("/api/v2/training-sessions/{$blockedSession->id}")
            ->assertNotFound();

        $this->actingAs($instructor)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($allowedGroup->id))
            ->assertCreated();

        $this->actingAs($instructor)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($blockedGroup->id))
            ->assertNotFound();

        $this->actingAs($instructor)
            ->putJson("/api/v2/training-sessions/{$blockedSession->id}", $this->sessionPayload($blockedGroup->id, [
                'period' => '5',
            ]))
            ->assertNotFound();

        $this->actingAs($instructor)
            ->get(route('export.training_sessions.pdf', ['id' => $blockedSession->id]))
            ->assertNotFound();
    }

    private function createTrainingGroup(int $schoolId, ?User $instructor = null, ?int $year = null, string $suffix = 'Base'): TrainingGroup
    {
        $year = $year ?? now()->year;

        $group = TrainingGroup::query()->create([
            'school_id' => $schoolId,
            'name' => "Grupo {$suffix}",
            'stage' => 'Cancha principal',
            'category' => ['Sub-12'],
            'days' => ['Lunes', 'Miércoles'],
            'schedules' => ['10:00AM - 11:00AM'],
            'year' => $year,
            'year_active' => $year,
        ]);

        if ($instructor) {
            $group->instructors()->attach($instructor->id, ['assigned_year' => $year]);
        }

        return $group->fresh();
    }

    private function createTrainingSession(TrainingGroup $group, User $creator, ?int $year = null, string $prefix = 'EX'): TrainingSession
    {
        $year = $year ?? now()->year;

        $session = TrainingSession::query()->create([
            'school_id' => $group->school_id,
            'user_id' => $creator->id,
            'training_group_id' => $group->id,
            'year' => $year,
            'period' => '1',
            'session' => '1',
            'date' => "{$year}-01-15",
            'hour' => '02:00 PM',
            'training_ground' => 'Cancha principal',
            'material' => 'Conos',
            'warm_up' => 'Movilidad',
            'back_to_calm' => '5',
            'players' => '18',
            'absences' => 'Sin ausencias',
            'incidents' => 'Sin incidentes',
            'feedback' => 'Buen trabajo',
        ]);

        $session->tasks()->createMany($this->makeTasks($prefix));

        return $session->fresh();
    }

    private function sessionPayload(int $trainingGroupId, array $overrides = []): array
    {
        return array_replace_recursive([
            'training_group_id' => $trainingGroupId,
            'period' => '1',
            'session' => '1',
            'date' => now()->format('Y-m-d'),
            'hour' => '02:00 PM',
            'training_ground' => 'Cancha principal',
            'material' => 'Conos y petos',
            'warm_up' => 'Activación general',
            'back_to_calm' => '5',
            'players' => '18',
            'absences' => 'Sin ausencias',
            'incidents' => 'Sin incidentes',
            'feedback' => 'Sesión positiva',
            'tasks' => $this->makeTasks('EX'),
        ], $overrides);
    }

    private function makeTasks(string $prefix): array
    {
        return [
            [
                'task_number' => 1,
                'task_name' => "{$prefix}1",
                'general_objective' => 'Objetivo 1',
                'specific_goal' => 'Meta 1',
                'content_one' => 'Contenido 1',
                'content_two' => 'Contenido 2',
                'content_three' => 'Contenido 3',
                'ts' => '9',
                'sr' => '2',
                'tt' => '20',
                'observations' => 'Observación 1',
            ],
            [
                'task_number' => 2,
                'task_name' => "{$prefix}2",
                'general_objective' => 'Objetivo 2',
                'specific_goal' => 'Meta 2',
                'content_one' => 'Contenido 4',
                'content_two' => 'Contenido 5',
                'content_three' => 'Contenido 6',
                'ts' => '10',
                'sr' => '3',
                'tt' => '22',
                'observations' => 'Observación 2',
            ],
            [
                'task_number' => 3,
                'task_name' => "{$prefix}3",
                'general_objective' => 'Objetivo 3',
                'specific_goal' => 'Meta 3',
                'content_one' => 'Contenido 7',
                'content_two' => 'Contenido 8',
                'content_three' => 'Contenido 9',
                'ts' => '11',
                'sr' => '4',
                'tt' => '24',
                'observations' => 'Observación 3',
            ],
        ];
    }

    private function createSchoolUserWithRole(int $schoolId, array $roles, string $email): User
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

    private function createSuperAdminForSchool(int $schoolId): User
    {
        return $this->createSchoolUserWithRole(
            $schoolId,
            ['super-admin'],
            sprintf('superadmin-training-%s@example.com', uniqid())
        );
    }
}
