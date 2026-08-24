<?php

declare(strict_types=1);

namespace App\Service\Portal;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\SkillsControl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GuardianPlayerFeedbackService
{
    public function forInscription(Inscription $inscription): Collection
    {
        return $this->attendanceFeedback($inscription)
            ->concat($this->competitionFeedback($inscription))
            ->sortByDesc(fn (array $feedback) => $feedback['event_date'])
            ->values();
    }

    private function attendanceFeedback(Inscription $inscription): Collection
    {
        $query = Assist::query()
            ->where('inscription_id', $inscription->id)
            ->whereNotNull('observations')
            ->with(['trainingGroup' => fn ($groupQuery) => $groupQuery->withTrashed()]);

        $this->whereJsonHasMeaningfulText($query);

        return $query->get()->flatMap(function ($assist): Collection {
            return collect((array) $assist->observations)
                ->map(function ($observation, $date) use ($assist): ?array {
                    $observation = $this->normalizeText($observation);

                    if ($observation === null) {
                        return null;
                    }

                    $group = $assist->trainingGroup;

                    return [
                        'id' => "attendance-{$assist->id}-{$date}",
                        'source' => 'attendance',
                        'source_label' => 'Asistencia',
                        'event_date' => (string) $date,
                        'created_at' => null,
                        'group_name' => $group?->full_group ?? $group?->name,
                        'observation' => $observation,
                    ];
                })
                ->filter();
        });
    }

    private function competitionFeedback(Inscription $inscription): Collection
    {
        return SkillsControl::query()
            ->where('inscription_id', $inscription->id)
            ->where(function (Builder $query): void {
                $query->where(function (Builder $observationQuery): void {
                    $observationQuery
                        ->whereNotNull('skills_control.observation')
                        ->whereRaw("TRIM(skills_control.observation) <> ''");
                })->orWhereHas('match', function (Builder $gameQuery): void {
                    $gameQuery
                        ->whereNotNull('games.general_concept')
                        ->whereRaw("TRIM(games.general_concept) <> ''");
                });
            })
            ->with([
                'match.tournament',
                'match.competitionGroup.professor',
                'match.competitionGroup.tournament',
            ])
            ->get()
            ->map(function ($control): ?array {
                $game = $control->match;
                $playerObservation = $this->normalizeText($control->observation);
                $groupObservation = $this->normalizeText($game?->general_concept);

                if ($game === null || ($playerObservation === null && $groupObservation === null)) {
                    return null;
                }

                $score = $game->final_score_array;

                return [
                    'id' => "competition-{$control->id}",
                    'source' => 'competition',
                    'source_label' => 'Competencia',
                    'event_date' => (string) $game->date,
                    'created_at' => optional($control->created_at)?->toISOString(),
                    'group_name' => $game->competitionGroup?->full_name,
                    'tournament_name' => $game->tournament?->name ?? $game->competitionGroup?->tournament?->name,
                    'coach_name' => $game->competitionGroup?->professor?->name,
                    'match_number' => $game->num_match,
                    'position' => $this->normalizeText($control->position),
                    'rival_name' => $game->rival_name,
                    'score' => $score ? [
                        'team' => $score->soccer,
                        'rival' => $score->rival,
                    ] : null,
                    'player_observation' => $playerObservation,
                    'group_observation' => $groupObservation,
                ];
            })
            ->filter();
    }

    private function whereJsonHasMeaningfulText(Builder $query): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $query->whereRaw(<<<'SQL'
                EXISTS (
                    SELECT 1
                    FROM json_each(
                        CASE
                            WHEN json_valid(assists.observations) THEN assists.observations
                            ELSE '{}'
                        END
                    ) AS observation_values
                    WHERE TRIM(COALESCE(CAST(observation_values.value AS TEXT), '')) <> ''
                )
                SQL);

            return;
        }

        if ($driver === 'mysql') {
            $query->whereRaw(<<<'SQL'
                EXISTS (
                    SELECT 1
                    FROM JSON_TABLE(
                        IF(JSON_VALID(assists.observations), assists.observations, JSON_OBJECT()),
                        '$.*' COLUMNS (observation_text TEXT PATH '$')
                    ) AS observation_values
                    WHERE TRIM(COALESCE(observation_values.observation_text, '')) <> ''
                )
                SQL);

            return;
        }

        $query->whereNotIn('assists.observations', ['', '{}', 'null']);
    }

    private function normalizeText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
