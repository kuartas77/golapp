<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\EvaluationTemplateCriterion;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\MethodologyRecord;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

final class InstructorMonthlyEditLockTest extends TestCase
{
    private const LOCK_MESSAGE = 'Este periodo ya está cerrado para instructores. Solicita a la escuela una corrección administrativa.';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-01 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function testInstructorAttendanceMutationsAreLimitedToCurrentMonthWhenEnabled(): void
    {
        $this->enableInstructorMonthlyLock();
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'lock-attendance@example.com');
        $group = $this->createTrainingGroup((int) $this->school['id'], $instructor);
        $inscription = $this->createInscription($group);
        $juneAssist = $this->createAssist($group, $inscription, 6);
        $julyAssist = $this->createAssist($group, $inscription, 7);

        $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson("/api/v2/assists/{$juneAssist->id}", [
                '_method' => 'PUT',
                'id' => $juneAssist->id,
                'assistance_one' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);

        $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson('/api/v2/assists/bulk-update', [
                'assist_ids' => [$juneAssist->id],
                'training_group_id' => $group->id,
                'month' => 6,
                'year' => 2026,
                'column' => 'assistance_one',
                'value' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);

        $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson("/api/v2/assists/{$julyAssist->id}", [
                '_method' => 'PUT',
                'id' => $julyAssist->id,
                'assistance_one' => 1,
            ])
            ->assertOk();
    }

    public function testSchoolUserCanCorrectPastAttendanceWhenInstructorLockIsEnabled(): void
    {
        $this->enableInstructorMonthlyLock();
        $group = $this->createTrainingGroup((int) $this->school['id']);
        $inscription = $this->createInscription($group);
        $juneAssist = $this->createAssist($group, $inscription, 6);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson("/api/v2/assists/{$juneAssist->id}", [
                '_method' => 'PUT',
                'id' => $juneAssist->id,
                'assistance_one' => 1,
            ])
            ->assertOk();
    }

    public function testInstructorCanEditPastAttendanceWhenSettingIsDisabled(): void
    {
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'lock-disabled@example.com');
        $group = $this->createTrainingGroup((int) $this->school['id'], $instructor);
        $inscription = $this->createInscription($group);
        $juneAssist = $this->createAssist($group, $inscription, 6);

        $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson("/api/v2/assists/{$juneAssist->id}", [
                '_method' => 'PUT',
                'id' => $juneAssist->id,
                'assistance_one' => 1,
            ])
            ->assertOk();
    }

    public function testInstructorCannotMutateClosedTrainingSessionPeriod(): void
    {
        $this->enableInstructorMonthlyLock();
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'lock-session@example.com');
        $group = $this->createTrainingGroup((int) $this->school['id'], $instructor);
        $session = $this->createTrainingSession($group, '2026-06-03', $instructor);

        $this->actingAs($instructor)
            ->postJson('/api/v2/training-sessions', $this->trainingSessionPayload($group, '2026-06-10'))
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);

        $this->actingAs($instructor)
            ->putJson("/api/v2/training-sessions/{$session->id}", $this->trainingSessionPayload($group, '2026-07-01'))
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);
    }

    public function testInstructorCannotMutateClosedMatchPeriod(): void
    {
        $this->enableInstructorMonthlyLock();
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'lock-match@example.com');
        $competitionGroup = $this->createCompetitionGroup((int) $this->school['id'], $instructor);
        $match = Game::query()->create($this->matchAttributes($competitionGroup, '2026-06-12'));

        $this->actingAs($instructor)
            ->postJson('/api/v2/matches', $this->matchPayload($competitionGroup, '2026-06-15'))
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);

        $this->actingAs($instructor)
            ->putJson("/api/v2/matches/{$match->id}", $this->matchPayload($competitionGroup, '2026-07-01', true, $match->id))
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);
    }

    public function testInstructorCannotMutateClosedEvaluationPeriod(): void
    {
        $this->enableInstructorMonthlyLock();
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'lock-evaluation@example.com');
        $group = $this->createTrainingGroup((int) $this->school['id'], $instructor);
        $fixture = $this->createEvaluationFixture($group, $instructor, '2026-06-20 08:00:00');

        $this->actingAs($instructor)
            ->putJson("/api/v2/player-evaluations/{$fixture['evaluation']->id}", [
                'evaluation_period_id' => $fixture['period']->id,
                'evaluation_template_id' => $fixture['template']->id,
                'evaluation_type' => 'periodic',
                'status' => 'draft',
                'evaluated_at' => '2026-07-01T08:00',
                'scores' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);

        $this->actingAs($instructor)
            ->deleteJson("/api/v2/player-evaluations/{$fixture['evaluation']->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);
    }

    public function testInstructorCannotMutateClosedMethodologyRecordPeriod(): void
    {
        $this->enableInstructorMonthlyLock();
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'lock-methodology@example.com');
        $record = MethodologyRecord::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $instructor->id,
            'training_group_id' => null,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'Plan cerrado',
            'fields' => ['objective' => 'Trabajo técnico'],
            'diagrams' => ['initial_phase' => []],
        ]);
        $record->forceFill([
            'created_at' => '2026-06-20 08:00:00',
            'updated_at' => '2026-06-20 08:00:00',
        ])->save();

        $this->actingAs($instructor)
            ->putJson("/api/v2/methodology-records/{$record->id}", [
                'training_group_id' => null,
                'type' => MethodologyRecord::TYPE_PLANNING,
                'title' => 'Plan actualizado',
                'fields' => ['objective' => 'Cambio'],
                'diagrams' => ['initial_phase' => []],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);

        $this->actingAs($instructor)
            ->deleteJson("/api/v2/methodology-records/{$record->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', self::LOCK_MESSAGE);
    }

    private function enableInstructorMonthlyLock(): void
    {
        SettingValue::query()->updateOrCreate(
            [
                'school_id' => $this->school['id'],
                'setting_key' => Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED,
            ],
            ['value' => '1']
        );

        School::forgetCachedSchool((int) $this->school['id']);
    }

    private function createSchoolScopedUser(int $schoolId, array $roles, string $email): User
    {
        $user = $this->createUser([
            'email' => $email,
            'school_id' => $schoolId,
        ], $roles);

        SchoolUser::query()->create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function createTrainingGroup(int $schoolId, ?User $instructor = null): TrainingGroup
    {
        $group = TrainingGroup::query()->create([
            'school_id' => $schoolId,
            'name' => 'Grupo Lock '.fake()->unique()->numberBetween(100, 999),
            'stage' => 'Cancha principal',
            'category' => ['Sub-12'],
            'days' => ['Miércoles'],
            'schedules' => ['08:00AM - 09:00AM'],
            'year' => 2026,
            'year_active' => 2026,
        ]);

        if ($instructor) {
            $group->instructors()->attach($instructor->id, ['assigned_year' => 2026]);
        }

        return $group->fresh();
    }

    private function createInscription(TrainingGroup $group): Inscription
    {
        $player = Player::factory()->create([
            'school_id' => $group->school_id,
            'unique_code' => 'LOCK-'.fake()->unique()->numberBetween(1000, 9999),
        ]);

        return Inscription::factory()->create([
            'school_id' => $group->school_id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => 2026,
            'start_date' => '2026-01-01',
        ]);
    }

    private function createAssist(TrainingGroup $group, Inscription $inscription, int $month): Assist
    {
        return Assist::query()->create([
            'school_id' => $group->school_id,
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => 2026,
            'month' => $month,
        ]);
    }

    private function createTrainingSession(TrainingGroup $group, string $date, User $creator): TrainingSession
    {
        return TrainingSession::query()->create([
            'school_id' => $group->school_id,
            'user_id' => $creator->id,
            'training_group_id' => $group->id,
            'year' => 2026,
            'period' => '1',
            'session' => '1',
            'date' => $date,
            'hour' => '08:00 AM',
            'training_ground' => 'Cancha',
        ]);
    }

    private function trainingSessionPayload(TrainingGroup $group, string $date): array
    {
        return [
            'training_group_id' => $group->id,
            'period' => '1',
            'session' => '2',
            'date' => $date,
            'hour' => '08:00 AM',
            'training_ground' => 'Cancha',
            'sync_attendance' => true,
            'absence_inscription_ids' => [],
            'tasks' => [
                ['task_number' => 1, 'task_name' => 'Ejercicio 1'],
                ['task_number' => 2, 'task_name' => null],
                ['task_number' => 3, 'task_name' => null],
            ],
        ];
    }

    private function createCompetitionGroup(int $schoolId, User $instructor): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'name' => 'Torneo Lock '.fake()->unique()->numberBetween(100, 999),
            'school_id' => $schoolId,
        ]);

        return CompetitionGroup::query()->create([
            'name' => 'Competencia Lock',
            'year' => '2026',
            'tournament_id' => $tournament->id,
            'user_id' => $instructor->id,
            'category' => '2010-2011',
            'school_id' => $schoolId,
        ])->load('tournament');
    }

    private function matchAttributes(CompetitionGroup $competitionGroup, string $date): array
    {
        return [
            'tournament_id' => $competitionGroup->tournament_id,
            'competition_group_id' => $competitionGroup->id,
            'date' => $date,
            'hour' => '08:00 AM',
            'num_match' => '1',
            'place' => 'Cancha',
            'rival_name' => 'Rival',
            'status' => Game::STATUS_SCHEDULED,
            'school_id' => $competitionGroup->school_id,
        ];
    }

    private function matchPayload(CompetitionGroup $competitionGroup, string $date, bool $isUpdate = false, ?int $gameId = null): array
    {
        $payload = $this->matchAttributes($competitionGroup, $date);
        $payload['skill_controls'] = [];

        if ($isUpdate) {
            $payload['id'] = $gameId;
        }

        return $payload;
    }

    private function createEvaluationFixture(TrainingGroup $group, User $instructor, string $evaluatedAt): array
    {
        $inscription = $this->createInscription($group);
        $period = EvaluationPeriod::query()->create([
            'name' => 'Corte Lock',
            'code' => 'LOCK',
            'year' => 2026,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
            'sort_order' => 1,
            'is_active' => true,
            'school_id' => $group->school_id,
        ]);
        $template = EvaluationTemplate::query()->create([
            'name' => 'Plantilla Lock',
            'year' => 2026,
            'status' => 'active',
            'version' => 1,
            'training_group_id' => $group->id,
            'created_by' => $instructor->id,
            'school_id' => $group->school_id,
        ]);
        EvaluationTemplateCriterion::query()->create([
            'evaluation_template_id' => $template->id,
            'code' => 'lock',
            'dimension' => 'General',
            'name' => 'Criterio',
            'score_type' => 'numeric',
            'min_score' => 1,
            'max_score' => 5,
            'weight' => 1,
            'sort_order' => 1,
            'is_required' => false,
        ]);
        $evaluation = PlayerEvaluation::query()->create([
            'school_id' => $group->school_id,
            'inscription_id' => $inscription->id,
            'evaluation_period_id' => $period->id,
            'evaluation_template_id' => $template->id,
            'evaluator_user_id' => $instructor->id,
            'evaluation_type' => 'periodic',
            'status' => 'draft',
            'evaluated_at' => $evaluatedAt,
        ]);

        return compact('period', 'template', 'evaluation');
    }
}
