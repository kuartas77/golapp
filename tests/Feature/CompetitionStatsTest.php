<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\SchoolUser;
use App\Models\SkillsControl;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Service\Player\PlayerStatsService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CompetitionStatsTest extends TestCase
{
    public function test_ranking_calculates_metrics_and_excludes_scheduled_and_invalid_scores(): void
    {
        $tournament = $this->createTournament((int) $this->school['id']);
        $leader = $this->createGroup($tournament, $this->user->id, 'Líder');
        $other = $this->createGroup($tournament, $this->user->id, 'Segundo');

        $this->createGame($leader, ['soccer' => '2', 'rival' => '1'], Game::STATUS_PLAYED);
        $this->createGame($leader, ['local' => 0, 'visitor' => 0], Game::STATUS_PLAYED);
        $this->createGame($leader, ['soccer' => 0, 'rival' => 8], Game::STATUS_SCHEDULED);
        $this->createGame($leader, ['soccer' => 'inválido', 'rival' => 1], Game::STATUS_PLAYED);
        $this->createGame($other, ['local' => 1, 'visitor' => 2], Game::STATUS_PLAYED);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/competition-stats?year='.now()->year)
            ->assertOk()
            ->assertJsonPath('summary.played', 3)
            ->assertJsonPath('summary.wins', 1)
            ->assertJsonPath('summary.draws', 1)
            ->assertJsonPath('summary.losses', 1)
            ->assertJsonPath('summary.points', 4)
            ->assertJsonPath('data_quality.excluded_invalid_scores', 1)
            ->assertJsonPath('groups.0.id', $leader->id)
            ->assertJsonPath('groups.0.played', 2)
            ->assertJsonPath('groups.0.points', 4)
            ->assertJsonPath('groups.0.clean_sheets', 1);

        $this->assertCount(2, $response->json('groups'));
    }

    public function test_detail_uses_historical_tournament_and_returns_recent_matches(): void
    {
        $currentTournament = $this->createTournament((int) $this->school['id'], 'Actual');
        $historicalTournament = $this->createTournament((int) $this->school['id'], 'Histórico');
        $group = $this->createGroup($currentTournament, $this->user->id, 'Histórico');
        $match = $this->createGame($group, ['soccer' => 4, 'rival' => 0], Game::STATUS_PLAYED, $historicalTournament);

        $this->actingAs($this->user)
            ->getJson("/api/v2/competition-stats/groups/{$group->id}?year=".now()->year)
            ->assertOk()
            ->assertJsonPath('summary.played', 1)
            ->assertJsonPath('summary.clean_sheets', 1)
            ->assertJsonPath('recent_matches.0.id', $match->id)
            ->assertJsonPath('recent_matches.0.tournament_name', $historicalTournament->name)
            ->assertJsonPath('recent_matches.0.result', 'win');
    }

    public function test_instructor_only_sees_assigned_groups_and_cannot_open_another_group(): void
    {
        $instructor = $this->createInstructor((int) $this->school['id'], 'stats-instructor@example.com');
        $otherInstructor = $this->createInstructor((int) $this->school['id'], 'stats-other@example.com');
        $tournament = $this->createTournament((int) $this->school['id']);
        $ownGroup = $this->createGroup($tournament, $instructor->id, 'Propio');
        $hiddenGroup = $this->createGroup($tournament, $otherInstructor->id, 'Oculto');
        $this->createGame($ownGroup, ['soccer' => 1, 'rival' => 0], Game::STATUS_PLAYED);
        $this->createGame($hiddenGroup, ['soccer' => 5, 'rival' => 0], Game::STATUS_PLAYED);

        $response = $this->actingAs($instructor)
            ->getJson('/api/v2/competition-stats?year='.now()->year)
            ->assertOk()
            ->assertJsonPath('groups.0.id', $ownGroup->id);

        $this->assertCount(1, $response->json('groups'));

        $this->actingAs($instructor)
            ->getJson("/api/v2/competition-stats/groups/{$hiddenGroup->id}?year=".now()->year)
            ->assertNotFound();
    }

    public function test_retired_group_with_played_matches_remains_visible(): void
    {
        $tournament = $this->createTournament((int) $this->school['id']);
        $group = $this->createGroup($tournament, $this->user->id, 'Retirado');
        $this->createGame($group, ['soccer' => 1, 'rival' => 1], Game::STATUS_PLAYED);
        $group->delete();

        $this->actingAs($this->user)
            ->getJson('/api/v2/competition-stats?year='.now()->year)
            ->assertOk()
            ->assertJsonPath('groups.0.id', $group->id)
            ->assertJsonPath('groups.0.is_retired', true);
    }

    public function test_player_statistics_ignore_skill_controls_from_scheduled_matches(): void
    {
        DB::connection()->getPdo()->sqliteCreateFunction(
            'regexp',
            fn (string $pattern, mixed $value): int => preg_match('/'.str_replace('/', '\\/', $pattern).'/', (string) $value),
        );
        $tournament = $this->createTournament((int) $this->school['id']);
        $group = $this->createGroup($tournament, $this->user->id, 'Jugadores');
        $player = Player::factory()->create(['school_id' => $this->school['id']]);
        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->toDateString(),
            'category' => '2010-2011',
            'training_group_id' => $this->schoolTrainingGroupId(),
        ]);
        $played = $this->createGame($group, ['soccer' => 2, 'rival' => 0], Game::STATUS_PLAYED);
        $scheduled = $this->createGame($group, ['soccer' => 9, 'rival' => 0], Game::STATUS_SCHEDULED);
        $this->createSkillControl($played, $inscription, 2);
        $this->createSkillControl($scheduled, $inscription, 9);

        $service = app(PlayerStatsService::class);
        $top = $service->getTopPlayersPayload((int) $this->school['id']);
        $detail = $service->getPlayerDetailPayload($player->id, (int) $this->school['id']);

        $this->assertSame('2', (string) $top['top_scorers']->first()->total_goles);
        $this->assertSame('2', (string) $detail['player']->total_goles);
        $this->assertCount(1, $detail['recent_matches']);
    }

    private function createTournament(int $schoolId, string $name = 'Torneo'): Tournament
    {
        return Tournament::query()->create([
            'name' => $name.' '.fake()->unique()->numberBetween(100, 999),
            'school_id' => $schoolId,
        ]);
    }

    private function createGroup(Tournament $tournament, int $userId, string $name): CompetitionGroup
    {
        return CompetitionGroup::query()->create([
            'name' => $name,
            'year' => '2010-2011',
            'category' => '2010-2011',
            'tournament_id' => $tournament->id,
            'user_id' => $userId,
            'school_id' => $tournament->school_id,
        ]);
    }

    private function createGame(CompetitionGroup $group, array $score, string $status, ?Tournament $tournament = null): Game
    {
        return Game::query()->create([
            'tournament_id' => $tournament?->id ?? $group->tournament_id,
            'competition_group_id' => $group->id,
            'date' => now()->toDateString(),
            'hour' => '08:00 AM',
            'num_match' => (string) fake()->unique()->numberBetween(1, 9999),
            'place' => 'Cancha Test',
            'rival_name' => 'Rival Test',
            'status' => $status,
            'final_score' => $score,
            'school_id' => $group->school_id,
        ]);
    }

    private function createInstructor(int $schoolId, string $email): User
    {
        $user = $this->createUser(['school_id' => $schoolId, 'email' => $email], [User::INSTRUCTOR]);
        SchoolUser::query()->create(['school_id' => $schoolId, 'user_id' => $user->id]);

        return $user;
    }

    private function schoolTrainingGroupId(): int
    {
        return (int) TrainingGroup::query()
            ->where('school_id', $this->school['id'])
            ->value('id');
    }

    private function createSkillControl(Game $game, Inscription $inscription, int $goals): SkillsControl
    {
        return SkillsControl::query()->create([
            'game_id' => $game->id,
            'inscription_id' => $inscription->id,
            'assistance' => 1,
            'titular' => 1,
            'played_approx' => 60,
            'position' => 'Delantero',
            'goals' => $goals,
            'goal_assists' => 0,
            'goal_saves' => 0,
            'red_cards' => 0,
            'yellow_cards' => 0,
            'qualification' => 4,
            'school_id' => $this->school['id'],
        ]);
    }
}
