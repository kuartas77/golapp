<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Player;
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

    public function testSessionPlanningCrudIsIsolatedFromStandardSessionsAndSharesDateUniqueness(): void
    {
        $school = School::findOrFail($this->school['id']);
        $school->forceFill(['school_permissions' => School::normalizeSchoolPermissions([
            ...$school->getResolvedSchoolPermissions(),
            'school.module.session_planning' => false,
        ])])->save();

        $this->actingAs($this->user)->get('/planificacion-sesiones')->assertForbidden();
        $this->actingAs($this->user)->postJson('/api/v2/session-plannings', [])->assertForbidden();
        $school->forceFill(['school_permissions' => School::normalizeSchoolPermissions([
            ...$school->getResolvedSchoolPermissions(),
            'school.module.session_planning' => true,
        ])])->save();
        $group = $this->createTrainingGroup($school->id, $this->user, suffix: 'Planificación');
        $payload = $this->plannedPayload($group->id);

        $this->actingAs($this->user)->get('/planificacion-sesiones')->assertOk();

        $response = $this->actingAs($this->user)->postJson('/api/v2/session-plannings', $payload)
            ->assertCreated()->assertJsonCount(4, 'data.phases')->assertJsonPath('data.phases.0.name', 'Activación');
        $id = (int) $response->json('data.id');

        $this->assertDatabaseHas('training_sessions', ['id' => $id, 'format' => TrainingSession::FORMAT_PLANNED]);
        $this->assertDatabaseCount('training_session_phases', 4);
        $this->actingAs($this->user)->getJson("/api/v2/training-sessions/{$id}")->assertNotFound();
        $this->actingAs($this->user)->getJson('/api/v2/datatables/training_sessions_enabled?draw=1&start=0&length=10')
            ->assertOk()->assertJsonMissing(['id' => $id]);

        $this->actingAs($this->user)->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, ['date' => $payload['date']]))
            ->assertUnprocessable()->assertJsonValidationErrors('date');

        $payload['phases'] = [$payload['phases'][0]];
        $this->actingAs($this->user)->putJson("/api/v2/session-plannings/{$id}", $payload)
            ->assertOk()->assertJsonCount(1, 'data.phases');
        $this->assertDatabaseCount('training_session_phases', 1);

        $this->actingAs($this->user)->get(route('session-plannings.pdf', $id))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function testSessionPlanningRequiresBetweenOneAndFourNamedPhases(): void
    {
        $school = School::findOrFail($this->school['id']);
        $school->forceFill(['school_permissions' => School::normalizeSchoolPermissions([
            ...$school->getResolvedSchoolPermissions(), 'school.module.session_planning' => true,
        ])])->save();
        $group = $this->createTrainingGroup($school->id, $this->user, suffix: 'Validación');

        $this->actingAs($this->user)->postJson('/api/v2/session-plannings', $this->plannedPayload($group->id, []))
            ->assertUnprocessable()->assertJsonValidationErrors('phases');
        $phases = array_fill(0, 5, ['name' => 'Fase', 'diagram' => []]);
        $this->actingAs($this->user)->postJson('/api/v2/session-plannings', $this->plannedPayload($group->id, $phases))
            ->assertUnprocessable()->assertJsonValidationErrors('phases');
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

        $longTaskName = 'Ejercicio tecnico de posesion orientada por carriles';
        $updatedTasks = $this->makeTasks('UP');
        $updatedTasks[0]['task_name'] = $longTaskName;

        $this->actingAs($this->user)
            ->putJson(
                "/api/v2/training-sessions/{$createdId}",
                $this->sessionPayload($group->id, [
                    'period' => '2',
                    'session' => '4',
                    'training_ground' => 'Cancha alterna',
                    'tasks' => $updatedTasks,
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
            [$longTaskName, 'UP2', 'UP3'],
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

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/training-sessions/{$createdId}")
            ->assertOk();

        $this->assertSoftDeleted('training_sessions', [
            'id' => $createdId,
            'school_id' => $this->school['id'],
        ]);
        $this->assertSame(
            3,
            TrainingSessionDetail::withTrashed()
                ->where('training_session_id', $createdId)
                ->whereNotNull('deleted_at')
                ->count()
        );
    }

    public function testOnlyFirstTrainingSessionTaskIsRequired(): void
    {
        $group = $this->createTrainingGroup($this->school['id'], $this->user);
        $tasks = $this->makeTasks('OP');
        $tasks[1]['task_name'] = '';
        $tasks[2]['task_name'] = null;

        $storeResponse = $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'tasks' => $tasks,
            ]))
            ->assertCreated()
            ->assertJsonPath('data.tasks.0.task_name', 'OP1')
            ->assertJsonPath('data.tasks.1.task_name', null)
            ->assertJsonPath('data.tasks.2.task_name', null);

        $this->assertSame(
            ['OP1'],
            TrainingSessionDetail::query()
                ->where('training_session_id', (int) $storeResponse->json('data.id'))
                ->orderBy('task_number')
                ->pluck('task_name')
                ->all()
        );

        $tasks[0]['task_name'] = '';

        $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'tasks' => $tasks,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tasks.0.task_name');
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

        $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->deleteJson("/api/v2/training-sessions/{$createdId}")
            ->assertOk();

        $this->assertSoftDeleted('training_sessions', [
            'id' => $createdId,
            'school_id' => $secondarySchool->id,
        ]);
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

        $this->actingAs($instructor)
            ->deleteJson("/api/v2/training-sessions/{$allowedSession->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('training_sessions', [
            'id' => $allowedSession->id,
            'deleted_at' => null,
        ]);
    }

    public function testSessionClosureSynchronizesAttendancesAndPreservesSpecialStatuses(): void
    {
        $group = $this->createTrainingGroup($this->school['id'], $this->user);
        $absent = $this->createActiveInscription($group, 'Ausente');
        $attendee = $this->createActiveInscription($group, 'Asistente');
        $excused = $this->createActiveInscription($group, 'Excusado');
        $classDay = $this->currentClassDay($group);

        Assist::query()->updateOrCreate([
            'training_group_id' => $group->id,
            'inscription_id' => $excused->id,
            'year' => now()->year,
            'month' => now()->month,
            'school_id' => $this->school['id'],
        ], [
            $classDay['column'] => 3,
        ]);

        $context = $this->actingAs($this->user)
            ->getJson('/api/v2/training-sessions/attendance-context?'.http_build_query([
                'training_group_id' => $group->id,
                'date' => $classDay['date'],
            ]))
            ->assertOk();

        $this->assertCount(2, $context->json('data.players'));
        $this->assertSame('Excusa', $context->json('data.protected_players.0.status_label'));

        $response = $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'date' => $classDay['date'],
                'sync_attendance' => true,
                'absence_inscription_ids' => [$absent->id],
            ]))
            ->assertCreated()
            ->assertJsonPath('data.players', '1')
            ->assertJsonPath('data.attendance_synced', true);

        $session = TrainingSession::findOrFail((int) $response->json('data.id'));
        $this->assertSame([$absent->id], $session->absence_inscription_ids);
        $this->assertNotNull($session->attendance_synced_at);

        $this->assertSame(2, (int) $this->assistFor($absent, $group)->{$classDay['column']});
        $this->assertSame(1, (int) $this->assistFor($attendee, $group)->{$classDay['column']});
        $this->assertSame(3, (int) $this->assistFor($excused, $group)->{$classDay['column']});

        $this->actingAs($this->user)
            ->putJson("/api/v2/training-sessions/{$session->id}", $this->sessionPayload($group->id, [
                'date' => $classDay['date'],
                'sync_attendance' => true,
                'absence_inscription_ids' => [$attendee->id],
            ]))
            ->assertOk();

        $this->assertSame(1, (int) $this->assistFor($absent, $group)->{$classDay['column']});
        $this->assertSame(2, (int) $this->assistFor($attendee, $group)->{$classDay['column']});
        $this->assertSame(3, (int) $this->assistFor($excused, $group)->{$classDay['column']});
    }

    public function testSyncedSessionRejectsIdentityChangesDuplicatesAndHistoricalAttendance(): void
    {
        $group = $this->createTrainingGroup($this->school['id'], $this->user);
        $this->createActiveInscription($group, 'Activo');
        $classDays = classDays(now()->year, now()->month, array_map('dayToNumber', $group->explode_days));
        $firstDay = $classDays->first();
        $secondDay = $classDays->skip(1)->first();

        $created = $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'date' => $firstDay['date'],
                'sync_attendance' => true,
                'absence_inscription_ids' => [],
            ]))
            ->assertCreated();

        $sessionId = (int) $created->json('data.id');

        $this->actingAs($this->user)
            ->putJson("/api/v2/training-sessions/{$sessionId}", $this->sessionPayload($group->id, [
                'date' => $secondDay['date'],
                'sync_attendance' => true,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');

        $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'date' => $firstDay['date'],
                'sync_attendance' => true,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');

        $historicalDay = classDays(now()->subYear()->year, 1, array_map('dayToNumber', $group->explode_days))->first();
        $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'date' => $historicalDay['date'],
                'sync_attendance' => true,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }

    public function testExistingSessionSynchronizesAutomaticallyAndDeletionKeepsAttendance(): void
    {
        $group = $this->createTrainingGroup($this->school['id'], $this->user);
        $inscription = $this->createActiveInscription($group, 'Legado');
        $legacySession = $this->createTrainingSession($group, $this->user);
        $classDay = $this->currentClassDay($group);

        $this->actingAs($this->user)
            ->putJson("/api/v2/training-sessions/{$legacySession->id}", $this->sessionPayload($group->id, [
                'date' => $classDay['date'],
                'feedback' => 'Edición con sincronización automática',
                'absences' => 'Texto histórico conservado',
                'sync_attendance' => false,
                'absence_inscription_ids' => [$inscription->id],
            ]))
            ->assertOk()
            ->assertJsonPath('data.attendance_synced', true);

        $legacySession->refresh();
        $this->assertNotNull($legacySession->attendance_synced_at);
        $this->assertSame([$inscription->id], $legacySession->absence_inscription_ids);
        $this->assertSame('Texto histórico conservado', $legacySession->absences);

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/training-sessions/{$legacySession->id}")
            ->assertOk();

        $this->assertSame(2, (int) $this->assistFor($inscription, $group)->{$classDay['column']});
    }

    public function testAttendanceContextRejectsIneligiblePlayersAndUnscheduledDates(): void
    {
        $group = $this->createTrainingGroup($this->school['id'], $this->user);
        $otherGroup = $this->createTrainingGroup($this->school['id'], $this->user, suffix: 'Otro');
        $eligible = $this->createActiveInscription($group, 'Elegible');
        $preInscription = $this->createActiveInscription($group, 'Preinscrito', ['pre_inscription' => true]);
        $futureInscription = $this->createActiveInscription($group, 'Futuro', [
            'start_date' => now()->endOfYear()->toDateString(),
        ]);
        $otherInscription = $this->createActiveInscription($otherGroup, 'Otro grupo');
        $classDay = $this->currentClassDay($group);

        $context = $this->actingAs($this->user)
            ->getJson('/api/v2/training-sessions/attendance-context?'.http_build_query([
                'training_group_id' => $group->id,
                'date' => $classDay['date'],
            ]))
            ->assertOk();

        $this->assertSame([$eligible->id], collect($context->json('data.players'))->pluck('value')->all());

        foreach ([$preInscription, $futureInscription, $otherInscription] as $ineligible) {
            $this->actingAs($this->user)
                ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                    'date' => $classDay['date'],
                    'session' => 'invalid-'.$ineligible->id,
                    'sync_attendance' => true,
                    'absence_inscription_ids' => [$ineligible->id],
                ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('absence_inscription_ids');
        }

        $unscheduledDate = now()->startOfMonth();
        while (in_array($unscheduledDate->isoWeekday(), [1, 3], true)) {
            $unscheduledDate->addDay();
        }

        $this->actingAs($this->user)
            ->postJson('/api/v2/training-sessions', $this->sessionPayload($group->id, [
                'date' => $unscheduledDate->toDateString(),
                'sync_attendance' => true,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
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

    private function createActiveInscription(TrainingGroup $group, string $name, array $overrides = []): Inscription
    {
        $player = Player::factory()->create([
            'school_id' => $group->school_id,
            'names' => $name,
            'last_names' => 'Prueba',
            'unique_code' => fake()->unique()->numerify('TS-#####'),
        ]);

        return Inscription::factory()->create(array_merge([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'school_id' => $group->school_id,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->toDateString(),
            'pre_inscription' => false,
        ], $overrides));
    }

    private function currentClassDay(TrainingGroup $group): array
    {
        return classDays(now()->year, now()->month, array_map('dayToNumber', $group->explode_days))->first();
    }

    private function assistFor(Inscription $inscription, TrainingGroup $group): Assist
    {
        return Assist::query()
            ->where('training_group_id', $group->id)
            ->where('inscription_id', $inscription->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->firstOrFail();
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
            'date' => classDays(now()->year, now()->month, [1, 3])->first()['date'],
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

    private function plannedPayload(int $trainingGroupId, ?array $phases = null): array
    {
        $phases ??= collect(['Activación', 'Técnica', 'Táctica', 'Juego'])->map(fn ($name, $index) => [
            'position' => $index + 1, 'name' => $name, 'time' => '15 min', 'dosage' => '2 series',
            'description' => "Descripción {$name}",
            'diagram' => [['id' => "p{$index}", 'type' => 'player', 'x' => 50, 'y' => 32, 'label' => '']],
        ])->all();

        $payload = $this->sessionPayload($trainingGroupId);
        unset($payload['tasks']);
        $payload['phases'] = $phases;
        return $payload;
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
