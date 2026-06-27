<?php

declare(strict_types=1);

namespace App\Service\Competition;

use App\Models\CompetitionGroup;
use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CompetitionStatsService
{
    public function getRankingPayload(int $schoolId, array $filters = [], ?int $instructorId = null): array
    {
        $filters = $this->normalizeFilters($filters);
        $optionsRows = $this->baseQuery($schoolId, $instructorId)->get();
        $rows = $this->applyFilters($this->baseQuery($schoolId, $instructorId), $filters)->get();
        [$validRows, $invalidCount] = $this->normalizeRows($rows);

        $groups = $validRows
            ->groupBy('competition_group_id')
            ->map(fn (Collection $matches) => $this->groupPayload($matches))
            ->sort($this->rankingComparator())
            ->values();

        return [
            'summary' => $this->summaryPayload($validRows, $groups->count()),
            'groups' => $groups,
            'filters' => $filters,
            'options' => $this->optionsPayload($optionsRows),
            'data_quality' => ['excluded_invalid_scores' => $invalidCount],
        ];
    }

    public function getGroupPayload(int $groupId, int $schoolId, array $filters = [], ?int $instructorId = null): ?array
    {
        $group = CompetitionGroup::query()
            ->withTrashed()
            ->with(['professor', 'tournament'])
            ->where('school_id', $schoolId)
            ->when($instructorId, fn (Builder $query) => $query->where('user_id', $instructorId))
            ->find($groupId);

        if (! $group) {
            return null;
        }

        $filters = $this->normalizeFilters($filters, false);
        $groupQuery = $this->baseQuery($schoolId, $instructorId)
            ->where('games.competition_group_id', $groupId);
        $optionsRows = (clone $groupQuery)->get();
        $rows = $this->applyFilters($groupQuery, $filters)->get();
        [$validRows, $invalidCount] = $this->normalizeRows($rows);
        $summary = $this->summaryPayload($validRows, $validRows->isEmpty() ? 0 : 1);
        $recentMatches = $validRows
            ->sortByDesc(fn (object $match) => sprintf('%s-%010d', $match->date, $match->id))
            ->take(10)
            ->map(fn (object $match) => $this->matchPayload($match))
            ->values();

        return [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'category' => $group->category,
                'instructor_name' => $group->professor?->name,
                'is_retired' => $group->trashed(),
            ],
            'summary' => $summary,
            'result_distribution' => [
                'wins' => $summary['wins'],
                'draws' => $summary['draws'],
                'losses' => $summary['losses'],
            ],
            'goal_trend' => $recentMatches->reverse()->values(),
            'recent_matches' => $recentMatches,
            'filters' => $filters,
            'options' => $this->optionsPayload($optionsRows, false),
            'data_quality' => ['excluded_invalid_scores' => $invalidCount],
        ];
    }

    private function baseQuery(int $schoolId, ?int $instructorId): Builder
    {
        return Game::query()
            ->select([
                'games.id',
                'games.date',
                'games.tournament_id',
                'games.competition_group_id',
                'games.rival_name',
                'games.final_score',
                'competition_groups.name as competition_group_name',
                'competition_groups.category',
                'competition_groups.deleted_at as group_deleted_at',
                'users.name as instructor_name',
                'tournaments.name as tournament_name',
            ])
            ->join('competition_groups', 'competition_groups.id', '=', 'games.competition_group_id')
            ->join('tournaments', 'tournaments.id', '=', 'games.tournament_id')
            ->leftJoin('users', 'users.id', '=', 'competition_groups.user_id')
            ->where('games.school_id', $schoolId)
            ->where('games.status', Game::STATUS_PLAYED)
            ->when($instructorId, fn (Builder $query) => $query->where('competition_groups.user_id', $instructorId));
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->whereYear('games.date', $filters['year'])
            ->when($filters['tournament_id'], fn (Builder $builder, int $id) => $builder->where('games.tournament_id', $id))
            ->when($filters['category'] ?? null, fn (Builder $builder, string $category) => $builder->where('competition_groups.category', $category));
    }

    private function normalizeFilters(array $filters, bool $withCategory = true): array
    {
        $normalized = [
            'year' => isset($filters['year']) && $filters['year'] !== '' ? (int) $filters['year'] : now()->year,
            'tournament_id' => isset($filters['tournament_id']) && $filters['tournament_id'] !== ''
                ? (int) $filters['tournament_id']
                : null,
        ];

        if ($withCategory) {
            $normalized['category'] = isset($filters['category']) && $filters['category'] !== ''
                ? (string) $filters['category']
                : null;
        }

        return $normalized;
    }

    private function normalizeRows(Collection $rows): array
    {
        $invalidCount = 0;
        $validRows = $rows->map(function (Game $row) use (&$invalidCount) {
            $score = $row->final_score_array;
            $goalsFor = $this->normalizeGoalValue($score?->soccer ?? null);
            $goalsAgainst = $this->normalizeGoalValue($score?->rival ?? null);

            if ($goalsFor === null || $goalsAgainst === null) {
                $invalidCount++;

                return null;
            }

            $row->goals_for = $goalsFor;
            $row->goals_against = $goalsAgainst;
            $row->result = $goalsFor > $goalsAgainst ? 'win' : ($goalsFor === $goalsAgainst ? 'draw' : 'loss');

            return $row;
        })->filter()->values();

        return [$validRows, $invalidCount];
    }

    private function normalizeGoalValue(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number >= 0 && floor($number) === $number ? (int) $number : null;
    }

    private function groupPayload(Collection $matches): array
    {
        $first = $matches->first();
        $metrics = $this->metrics($matches);

        return [
            'id' => $first->competition_group_id,
            'name' => $first->competition_group_name,
            'category' => $first->category,
            'instructor_name' => $first->instructor_name,
            'is_retired' => $first->group_deleted_at !== null,
            'tournaments' => $matches->pluck('tournament_name')->filter()->unique()->values(),
            ...$metrics,
        ];
    }

    private function summaryPayload(Collection $matches, int $groupsCount): array
    {
        return ['groups_count' => $groupsCount, ...$this->metrics($matches)];
    }

    private function metrics(Collection $matches): array
    {
        $played = $matches->count();
        $wins = $matches->where('result', 'win')->count();
        $draws = $matches->where('result', 'draw')->count();
        $losses = $matches->where('result', 'loss')->count();
        $goalsFor = (int) $matches->sum('goals_for');
        $goalsAgainst = (int) $matches->sum('goals_against');
        $points = $wins * 3 + $draws;

        return [
            'played' => $played,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'goal_difference' => $goalsFor - $goalsAgainst,
            'points' => $points,
            'performance_percentage' => $played ? round($points / ($played * 3) * 100, 2) : 0.0,
            'goals_for_average' => $played ? round($goalsFor / $played, 2) : 0.0,
            'goals_against_average' => $played ? round($goalsAgainst / $played, 2) : 0.0,
            'clean_sheets' => $matches->where('goals_against', 0)->count(),
        ];
    }

    private function matchPayload(object $match): array
    {
        return [
            'id' => $match->id,
            'date' => $match->date,
            'tournament_id' => $match->tournament_id,
            'tournament_name' => $match->tournament_name,
            'rival_name' => $match->rival_name,
            'goals_for' => $match->goals_for,
            'goals_against' => $match->goals_against,
            'result' => $match->result,
            'result_label' => match ($match->result) {
                'win' => 'Victoria',
                'draw' => 'Empate',
                default => 'Derrota',
            },
        ];
    }

    private function optionsPayload(Collection $rows, bool $withCategories = true): array
    {
        $options = [
            'years' => $rows->pluck('date')->filter()->map(fn ($date) => (int) substr((string) $date, 0, 4))->unique()->sortDesc()->values(),
            'tournaments' => $rows->map(fn ($row) => ['value' => $row->tournament_id, 'label' => $row->tournament_name])
                ->unique('value')->sortBy('label')->values(),
        ];

        if ($withCategories) {
            $options['categories'] = $rows->pluck('category')->filter()->unique()->sort()->values();
        }

        return $options;
    }

    private function rankingComparator(): callable
    {
        return function (array $left, array $right): int {
            foreach (['points', 'goal_difference', 'goals_for', 'wins'] as $field) {
                $comparison = $right[$field] <=> $left[$field];

                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return strcasecmp($left['name'], $right['name']);
        };
    }
}
