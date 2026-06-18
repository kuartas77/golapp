<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\MethodologyRecord;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InstructorActivityReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAssistsDetailView();
    }

    public function test_instructor_activity_report_summarizes_monthly_activity(): void
    {
        $year = 2026;
        $month = 5;
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'activity-instructor@example.com');
        $inactiveInstructor = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'activity-inactive@example.com');
        $group = $this->createTrainingGroup((int) $this->school['id'], 'Actividad Sub 12');
        $group->instructors()->attach($instructor->id, ['assigned_year' => $year]);

        $inscription = $this->createInscription($group, $year, 'ACT-100');
        Assist::query()->create([
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year,
            'month' => $month,
            'assistance_one' => 1,
            'assistance_two' => 2,
            'assistance_three' => 1,
        ]);
        Assist::query()->create([
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year,
            'month' => 6,
            'assistance_one' => 1,
        ]);

        $competitionGroup = $this->createCompetitionGroup((int) $this->school['id'], $instructor, $year);
        $tournament = Tournament::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Liga Actividad',
        ]);
        Game::query()->create([
            'school_id' => $this->school['id'],
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => '2026-05-12',
            'hour' => '08:00',
            'num_match' => '1',
            'place' => 'Cancha 1',
            'rival_name' => 'Rival A',
            'final_score' => ['soccer' => 2, 'rival' => 1],
        ]);
        Game::query()->create([
            'school_id' => $this->school['id'],
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => '2026-06-12',
            'hour' => '08:00',
            'num_match' => '2',
            'place' => 'Cancha 1',
            'rival_name' => 'Rival B',
            'final_score' => ['soccer' => 0, 'rival' => 0],
        ]);

        TrainingSession::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $instructor->id,
            'training_group_id' => $group->id,
            'year' => $year,
            'period' => '1',
            'session' => '1',
            'date' => '2026-05-15',
            'hour' => '08:00',
        ]);
        TrainingSession::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $instructor->id,
            'training_group_id' => $group->id,
            'year' => $year,
            'period' => '1',
            'session' => '2',
            'date' => '2026-06-15',
            'hour' => '08:00',
        ]);

        foreach ([
            MethodologyRecord::TYPE_PLANNING,
            MethodologyRecord::TYPE_CHARACTERIZATION_SHEET,
            MethodologyRecord::TYPE_MONTHLY_REPORT,
            MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT,
        ] as $type) {
            $record = MethodologyRecord::query()->create([
                'school_id' => $this->school['id'],
                'user_id' => $instructor->id,
                'training_group_id' => $group->id,
                'type' => $type,
                'title' => "Registro {$type}",
                'fields' => [],
            ]);
            $record->forceFill([
                'created_at' => '2026-05-20 10:00:00',
                'updated_at' => '2026-05-20 10:00:00',
            ])->save();
        }
        $outOfMonthRecord = MethodologyRecord::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $instructor->id,
            'training_group_id' => $group->id,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'Fuera del mes',
            'fields' => [],
        ]);
        $outOfMonthRecord->forceFill([
            'created_at' => '2026-06-20 10:00:00',
            'updated_at' => '2026-06-20 10:00:00',
        ])->save();

        $otherSchool = School::factory()->create([
            'email' => 'activity-other-school@example.com',
            'slug' => 'activity-other-school',
        ]);
        $otherInstructor = $this->createSchoolScopedUser($otherSchool->id, ['instructor'], 'activity-other@example.com');
        $otherRecord = MethodologyRecord::query()->create([
            'school_id' => $otherSchool->id,
            'user_id' => $otherInstructor->id,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'No visible',
            'fields' => [],
        ]);
        $otherRecord->forceFill([
            'created_at' => '2026-05-20 10:00:00',
            'updated_at' => '2026-05-20 10:00:00',
        ])->save();
        $activityWithoutInscriptionYear = MethodologyRecord::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $instructor->id,
            'training_group_id' => $group->id,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'Actividad sin inscripcion anual',
            'fields' => [],
        ]);
        $activityWithoutInscriptionYear->forceFill([
            'created_at' => '2027-05-20 10:00:00',
            'updated_at' => '2027-05-20 10:00:00',
        ])->save();

        $metadata = $this->actingAs($this->user)
            ->getJson('/api/v2/reports/instructors/activity/metadata')
            ->assertOk();

        $this->assertContains(2026, collect($metadata->json('years'))->pluck('value')->all());
        $this->assertNotContains(2027, collect($metadata->json('years'))->pluck('value')->all());
        $this->assertContains($instructor->id, collect($metadata->json('instructors'))->pluck('value')->all());
        $this->assertContains($inactiveInstructor->id, collect($metadata->json('instructors'))->pluck('value')->all());
        $this->assertNotContains($otherInstructor->id, collect($metadata->json('instructors'))->pluck('value')->all());

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/reports/instructors/activity?draw=1&start=0&length=10&year=2026&month=5')
            ->assertOk();

        $rows = collect($response->json('data'))->keyBy('instructor_id');

        $this->assertSame('Mayo', $rows[$instructor->id]['month_label']);
        $this->assertSame('3/8', $rows[$instructor->id]['attendance_coverage']);
        $this->assertSame(1, (int) $rows[$instructor->id]['matches_count']);
        $this->assertSame(1, (int) $rows[$instructor->id]['training_sessions_count']);
        $this->assertSame(4, (int) $rows[$instructor->id]['methodology_total']);
        $this->assertSame(1, (int) $rows[$instructor->id]['methodology_planning_count']);
        $this->assertSame(1, (int) $rows[$instructor->id]['methodology_characterization_count']);
        $this->assertSame(1, (int) $rows[$instructor->id]['methodology_monthly_count']);
        $this->assertSame(1, (int) $rows[$instructor->id]['methodology_category_monthly_count']);

        $this->assertSame('Mayo', $rows[$inactiveInstructor->id]['month_label']);
        $this->assertSame('0/0', $rows[$inactiveInstructor->id]['attendance_coverage']);
        $this->assertArrayNotHasKey($otherInstructor->id, $rows->all());

        $filtered = $this->actingAs($this->user)
            ->getJson("/api/v2/reports/instructors/activity?draw=2&start=0&length=10&year=2026&month=5&instructor_id={$instructor->id}")
            ->assertOk();

        $this->assertCount(1, $filtered->json('data'));
        $this->assertSame($instructor->id, $filtered->json('data.0.instructor_id'));
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

    private function createTrainingGroup(int $schoolId, string $name): TrainingGroup
    {
        $group = TrainingGroup::query()->create([
            'school_id' => $schoolId,
            'name' => $name,
            'stage' => 'Formativo',
            'year' => '2012',
            'days' => ['Lunes', 'Miércoles'],
            'schedules' => ['08:00 - 09:00'],
            'year_active' => 2026,
        ]);

        return $group->fresh();
    }

    private function createCompetitionGroup(int $schoolId, User $instructor, int $year): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'school_id' => $schoolId,
            'name' => 'Torneo base',
        ]);

        return CompetitionGroup::query()->create([
            'school_id' => $schoolId,
            'name' => 'Competencia Sub 12',
            'year' => (string) $year,
            'tournament_id' => $tournament->id,
            'user_id' => $instructor->id,
            'category' => 'Sub 12',
        ]);
    }

    private function createInscription(TrainingGroup $group, int $year, string $uniqueCode): Inscription
    {
        $player = Player::factory()->create([
            'school_id' => $group->school_id,
            'unique_code' => $uniqueCode,
            'category' => '2012',
        ]);

        return Inscription::query()->create([
            'school_id' => $group->school_id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $year,
            'start_date' => "{$year}-01-15",
            'category' => '2012',
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);
    }

    private function createAssistsDetailView(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS vw_assists_detail');
        DB::statement('
            CREATE VIEW vw_assists_detail AS
            SELECT id as assist_id, training_group_id, inscription_id, school_id, year, month, 1 as session_number, assistance_one as status_id, created_at, updated_at
            FROM assists
            WHERE deleted_at IS NULL AND assistance_one IS NOT NULL
            UNION ALL
            SELECT id as assist_id, training_group_id, inscription_id, school_id, year, month, 2 as session_number, assistance_two as status_id, created_at, updated_at
            FROM assists
            WHERE deleted_at IS NULL AND assistance_two IS NOT NULL
            UNION ALL
            SELECT id as assist_id, training_group_id, inscription_id, school_id, year, month, 3 as session_number, assistance_three as status_id, created_at, updated_at
            FROM assists
            WHERE deleted_at IS NULL AND assistance_three IS NOT NULL
        ');
    }
}
