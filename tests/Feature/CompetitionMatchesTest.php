<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exports\MatchDetailExport;
use App\Models\CompetitionGroup;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\SchoolUser;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

final class CompetitionMatchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('skills_control', 'goal_assists')) {
            Schema::table('skills_control', function ($table): void {
                $table->smallInteger('goal_assists')->default(0);
                $table->smallInteger('goal_saves')->default(0);
            });
        }
    }

    public function test_create_returns404_for_competition_group_from_another_school(): void
    {
        [, $otherUser] = $this->createSchoolAndUser();
        $otherCompetitionGroup = $this->createCompetitionGroupForSchool(
            $otherUser->school_id,
            $otherUser->id
        );

        $this->actingAs($this->user)
            ->getJson("/api/v2/matches/0?competition_group={$otherCompetitionGroup->id}")
            ->assertNotFound();
    }

    public function test_instructor_can_access_matches_datatable_scoped_to_own_competition_groups(): void
    {
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'matches-instructor@example.com');
        $otherInstructor = $this->createSchoolScopedUser((int) $this->school['id'], [User::INSTRUCTOR], 'matches-other-instructor@example.com');

        $ownMatch = $this->createMatchForSchool((int) $this->school['id'], $instructor->id, ['soccer' => 2, 'rival' => 1]);
        $hiddenMatch = $this->createMatchForSchool((int) $this->school['id'], $otherInstructor->id, ['soccer' => 0, 'rival' => 1]);

        $response = $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/matches?draw=1&start=0&length=10&year='.now()->year)
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ownMatch->id, $ids);
        $this->assertNotContains($hiddenMatch->id, $ids);
    }

    public function test_edit_update_and_destroy_return404_for_match_from_another_school(): void
    {
        [$otherSchool, $otherUser] = $this->createSchoolAndUser();
        $otherMatch = $this->createMatchForSchool($otherSchool['id'], $otherUser->id, ['soccer' => 2, 'rival' => 1]);

        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);

        $this->actingAs($this->user)
            ->getJson("/api/v2/matches/{$otherMatch->id}")
            ->assertNotFound();

        $this->actingAs($this->user)
            ->putJson(
                "/api/v2/matches/{$otherMatch->id}",
                $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription, true, $otherMatch->id)
            )
            ->assertNotFound();

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/matches/{$otherMatch->id}")
            ->assertNotFound();
    }

    public function test_store_creates_skills_control_with_goal_assist_and_goal_saves_defaults(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);

        $this->actingAs($this->user)
            ->postJson(
                '/api/v2/matches',
                $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription)
            )
            ->assertOk()
            ->assertJsonPath('success', true);

        $match = Game::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('skills_control', [
            'game_id' => $match->id,
            'inscription_id' => $inscription->id,
            'goal_assists' => 0,
            'goal_saves' => 0,
        ]);
    }

    public function test_update_derives_skill_control_game_id_from_route_when_missing(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);
        $match = Game::query()->create([
            'tournament_id' => $competitionGroup->tournament_id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '08:00 AM',
            'num_match' => '8',
            'place' => 'Cancha Import',
            'rival_name' => 'Rival Import',
            'final_score' => ['soccer' => '0', 'rival' => '0'],
            'general_concept' => 'Antes de importar',
            'school_id' => $this->school['id'],
        ]);

        $payload = $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription);
        $payload['skill_controls'][0]['goals'] = '2';

        $this->actingAs($this->user)
            ->putJson("/api/v2/matches/{$match->id}", $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('skills_control', [
            'game_id' => $match->id,
            'inscription_id' => $inscription->id,
            'goals' => 2,
            'observation' => 'Ingreso desde test',
        ]);
    }

    public function test_exported_match_file_can_be_imported_with_inline_dropdown_values(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);
        $competitionGroup->inscriptions()->attach($inscription->id);
        $this->actingAs($this->user);

        $contents = Excel::raw(new MatchDetailExport($competitionGroup->id), ExcelWriter::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'match-import-');
        file_put_contents($path, $contents);

        try {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getSheet(0);
            $sheet->fromArray(
                ['Sí', 'Sí', 60, 'Defensa (Central)', 2, 1, 0, 1, 0, 5, 'Buen desempeño defensivo'],
                null,
                'C2'
            );
            (new Xlsx($spreadsheet))->save($path);

            $response = $this->postJson('/import/matches/0', [
                'file' => UploadedFile::fake()->createWithContent('partido.xlsx', file_get_contents($path)),
            ]);
        } finally {
            @unlink($path);
        }

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'skills_controls')
            ->assertJsonPath('skills_controls.0.inscription_id', $inscription->id)
            ->assertJsonPath('skills_controls.0.assistance', 1)
            ->assertJsonPath('skills_controls.0.position', 'Defensa (Central)')
            ->assertJsonPath('skills_controls.0.goals', 2)
            ->assertJsonPath('skills_controls.0.qualification', 5)
            ->assertJsonPath('skills_controls.0.observation', 'Buen desempeño defensivo');
    }

    public function test_create_and_edit_match_data_include_the_stored_player_photo(): void
    {
        Storage::fake('public');

        $photoPath = 'players/coachboard-player.jpg';
        Storage::disk('public')->put($photoPath, 'photo-content');

        $player = $this->createTestPlayer();
        $player->update(['photo' => $photoPath]);

        [$inscription] = $this->createInscriptionAndPayment($player);
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);
        $competitionGroup->inscriptions()->attach($inscription->id);

        $createResponse = $this->actingAs($this->user)
            ->getJson("/api/v2/matches/0?competition_group={$competitionGroup->id}")
            ->assertOk();

        $this->assertStringContainsString(
            $photoPath,
            $createResponse->json('skills_controls.0.player.photo_url')
        );

        $storeResponse = $this->postJson(
            '/api/v2/matches',
            $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription)
        )->assertOk();

        $editResponse = $this->getJson('/api/v2/matches/'.$storeResponse->json('match_id'))
            ->assertOk();

        $this->assertStringContainsString(
            $photoPath,
            $editResponse->json('skills_controls.0.player.photo_url')
        );
    }

    public function test_final_score_array_accessor_supports_current_and_legacy_formats(): void
    {
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);

        $currentFormatMatch = Game::query()->create([
            'tournament_id' => $competitionGroup->tournament_id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '08:00',
            'num_match' => '10',
            'place' => 'Cancha Actual',
            'rival_name' => 'Rival Actual',
            'final_score' => ['soccer' => '2', 'rival' => '1'],
            'general_concept' => 'Actual',
            'school_id' => $this->school['id'],
        ]);

        $legacyFormatMatch = Game::query()->create([
            'tournament_id' => $competitionGroup->tournament_id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '09:00',
            'num_match' => '11',
            'place' => 'Cancha Legacy',
            'rival_name' => 'Rival Legacy',
            'final_score' => ['local' => '3', 'visitor' => '2'],
            'general_concept' => 'Legacy',
            'school_id' => $this->school['id'],
        ]);

        $this->assertSame('2', $currentFormatMatch->final_score_array->soccer);
        $this->assertSame('1', $currentFormatMatch->final_score_array->rival);
        $this->assertSame('3', $legacyFormatMatch->final_score_array->soccer);
        $this->assertSame('2', $legacyFormatMatch->final_score_array->rival);
    }

    public function test_new_match_is_scheduled_without_a_placeholder_score(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);

        $response = $this->actingAs($this->user)->postJson(
            '/api/v2/matches',
            $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription)
        )->assertOk()->assertJsonPath('success', true);

        $match = Game::query()->findOrFail($response->json('match_id'));

        $this->assertSame(Game::STATUS_SCHEDULED, $match->status);
        $this->assertNull($match->final_score);
    }

    public function test_new_match_uses_and_accepts_the_default_qualification(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);
        $competitionGroup->inscriptions()->attach($inscription->id);

        $formResponse = $this->actingAs($this->user)
            ->getJson("/api/v2/matches/0?competition_group={$competitionGroup->id}")
            ->assertOk()
            ->assertJsonPath('skills_controls.0.qualification', 1);

        $payload = $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription);
        $payload['skill_controls'][0]['qualification'] = (string) $formResponse->json('skills_controls.0.qualification');

        $response = $this->actingAs($this->user)
            ->postJson('/api/v2/matches', $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $match = Game::query()->findOrFail($response->json('match_id'));

        $this->assertSame(Game::STATUS_SCHEDULED, $match->status);
        $this->assertSame('1', (string) $match->skillsControls()->firstOrFail()->qualification);
    }

    public function test_played_match_requires_a_non_future_date_and_non_negative_score(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);
        $match = $this->createMatchForSchool($this->school['id'], $this->user->id, ['soccer' => 0, 'rival' => 0]);
        $payload = $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription, true, $match->id);
        $payload['status'] = Game::STATUS_PLAYED;
        $payload['date'] = now()->addDay()->toDateString();
        $payload['final_score_school'] = '-1';

        $this->actingAs($this->user)
            ->putJson("/api/v2/matches/{$match->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'final_score.soccer']);
    }

    public function test_played_match_can_return_to_scheduled_without_losing_draft_data(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);
        $match = Game::query()->create([
            'tournament_id' => $competitionGroup->tournament_id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '08:00 AM',
            'num_match' => '9',
            'place' => 'Cancha Principal',
            'rival_name' => 'Rival Estado',
            'status' => Game::STATUS_PLAYED,
            'final_score' => ['soccer' => 3, 'rival' => 1],
            'school_id' => $this->school['id'],
        ]);
        $payload = $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription, true, $match->id);
        $payload['status'] = Game::STATUS_SCHEDULED;
        $payload['final_score_school'] = '3';
        $payload['final_score_rival'] = '1';

        $this->actingAs($this->user)
            ->putJson("/api/v2/matches/{$match->id}", $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $match->refresh();
        $this->assertSame(Game::STATUS_SCHEDULED, $match->status);
        $this->assertSame('3', (string) $match->final_score_array->soccer);
        $this->assertSame('1', (string) $match->final_score_array->rival);
    }

    private function validMatchPayload(
        Tournament $tournament,
        CompetitionGroup $competitionGroup,
        Inscription $inscription,
        bool $isUpdate = false,
        ?int $gameId = null
    ): array {
        $skillControl = [
            'id' => null,
            'inscription_id' => (string) $inscription->id,
            'assistance' => '1',
            'titular' => '1',
            'played_approx' => '30',
            'position' => 'MID',
            'goals' => '1',
            'goal_assists' => '0',
            'goal_saves' => '0',
            'yellow_cards' => '0',
            'red_cards' => '0',
            'qualification' => '4',
            'observation' => 'Ingreso desde test',
        ];

        if ($isUpdate) {
            $skillControl['game_id'] = (string) ($gameId ?? 0);
        }

        return [
            'competition_group_id' => (string) $competitionGroup->id,
            'tournament_id' => (string) $tournament->id,
            'num_match' => '1',
            'place' => 'Cancha Principal',
            'date' => now()->toDateString(),
            'hour' => '08:00 AM',
            'rival_name' => 'Rival Test',
            'final_score_school' => '1',
            'final_score_rival' => '0',
            'general_concept' => 'Buen partido',
            'skill_controls' => [
                $skillControl,
            ],
        ];
    }

    private function createCompetitionGroupForSchool(int $schoolId, int $userId): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'name' => 'Torneo '.$schoolId.'-'.fake()->unique()->numberBetween(100, 999),
            'school_id' => $schoolId,
        ]);

        return CompetitionGroup::query()->create([
            'name' => 'Grupo '.fake()->unique()->numberBetween(100, 999),
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $userId,
            'category' => '2010-2011',
            'school_id' => $schoolId,
        ])->load('tournament');
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

    private function createMatchForSchool(int $schoolId, int $userId, array $finalScore): Game
    {
        $competitionGroup = $this->createCompetitionGroupForSchool($schoolId, $userId);

        return Game::query()->create([
            'tournament_id' => $competitionGroup->tournament_id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '07:00 AM',
            'num_match' => '5',
            'place' => 'Cancha Externa',
            'rival_name' => 'Rival Externo',
            'final_score' => $finalScore,
            'general_concept' => 'Partido externo',
            'school_id' => $schoolId,
        ]);
    }

    private function createTestPlayer(): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'MC-'.fake()->unique()->numberBetween(1000, 9999),
        ]);
    }

    private function createInscriptionAndPayment(?Player $player = null, ?TrainingGroup $trainingGroup = null): array
    {
        $player = $player ?: $this->createTestPlayer();
        $trainingGroup = $trainingGroup ?: TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
        ]);

        $payment = Payment::query()
            ->where('inscription_id', $inscription->id)
            ->where('year', now()->year)
            ->firstOrFail();
        $payment->school_id = $this->school['id'];
        $payment->january = '2';
        $payment->february = '1';
        $payment->save();

        return [$inscription, $payment, $trainingGroup];
    }
}
