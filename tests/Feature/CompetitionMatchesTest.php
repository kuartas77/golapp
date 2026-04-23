<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CompetitionMatchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasColumn('skills_control', 'goal_assists')) {
            Schema::table('skills_control', function ($table): void {
                $table->smallInteger('goal_assists')->default(0);
                $table->smallInteger('goal_saves')->default(0);
            });
        }
    }

    public function testCreateReturns404ForCompetitionGroupFromAnotherSchool(): void
    {
        [, $otherUser] = $this->createSchoolAndUser();
        $otherCompetitionGroup = $this->createCompetitionGroupForSchool(
            $otherUser->school_id,
            $otherUser->id
        );

        $this->actingAs($this->user);

        $response = $this->get(route('matches.create', ['competition_group' => $otherCompetitionGroup->id]));

        $response->assertNotFound();
    }

    public function testEditUpdateAndDestroyReturn404ForMatchFromAnotherSchool(): void
    {
        [$otherSchool, $otherUser] = $this->createSchoolAndUser();
        $otherMatch = $this->createMatchForSchool($otherSchool['id'], $otherUser->id, ['soccer' => 2, 'rival' => 1]);

        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);

        $this->actingAs($this->user);

        $this->get(route('matches.edit', [$otherMatch->id]))->assertNotFound();

        $this->put(
            route('matches.update', [$otherMatch->id]),
            $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription) + ['ids' => ['']]
        )->assertNotFound();

        $this->delete(route('matches.destroy', [$otherMatch->id]))->assertNotFound();
    }

    public function testStoreCreatesSkillsControlWithGoalAssistAndGoalSavesDefaults(): void
    {
        [$inscription] = $this->createInscriptionAndPayment();
        $competitionGroup = $this->createCompetitionGroupForSchool($this->school['id'], $this->user->id);

        $this->actingAs($this->user);

        $response = $this->post(
            route('matches.store'),
            $this->validMatchPayload($competitionGroup->tournament, $competitionGroup, $inscription)
        );

        $response->assertRedirect(route('matches.index'));

        $match = Game::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('skills_control', [
            'game_id' => $match->id,
            'inscription_id' => $inscription->id,
            'goal_assists' => 0,
            'goal_saves' => 0,
        ]);
    }

    public function testFinalScoreArrayAccessorSupportsCurrentAndLegacyFormats(): void
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

    private function validMatchPayload(Tournament $tournament, CompetitionGroup $competitionGroup, Inscription $inscription): array
    {
        return [
            'name' => $competitionGroup->full_name,
            'competition_group_id' => (string) $competitionGroup->id,
            'tournament_id' => (string) $tournament->id,
            'user_id' => $this->user->name,
            'num_match' => '1',
            'place' => 'Cancha Principal',
            'date' => now()->toDateString(),
            'hour' => '08:00 AM',
            'rival_name' => 'Rival Test',
            'final_score' => [
                'soccer' => '1',
                'rival' => '0',
            ],
            'general_concept' => 'Buen partido',
            'inscriptions_id' => [
                0 => (string) $inscription->id,
            ],
            'assistance' => [
                0 => '1',
            ],
            'titular' => [
                0 => '1',
            ],
            'played_approx' => [
                0 => '30',
            ],
            'position' => [
                0 => 'MID',
            ],
            'goals' => [
                0 => '1',
            ],
            'goal_assists' => [
                0 => '0',
            ],
            'goal_saves' => [
                0 => '0',
            ],
            'yellow_cards' => [
                0 => '0',
            ],
            'red_cards' => [
                0 => '0',
            ],
            'qualification' => [
                0 => '4',
            ],
            'observation' => [
                0 => 'Ingreso desde test',
            ],
        ];
    }

    private function createCompetitionGroupForSchool(int $schoolId, int $userId): CompetitionGroup
    {
        $tournament = Tournament::query()->create([
            'name' => 'Torneo ' . $schoolId . '-' . fake()->unique()->numberBetween(100, 999),
            'school_id' => $schoolId,
        ]);

        return CompetitionGroup::query()->create([
            'name' => 'Grupo ' . fake()->unique()->numberBetween(100, 999),
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $userId,
            'category' => '2010-2011',
            'school_id' => $schoolId,
        ])->load('tournament');
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
            'unique_code' => 'MC-' . fake()->unique()->numberBetween(1000, 9999),
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
