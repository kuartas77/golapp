<?php

namespace App\Service\Player;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlayerStatsService
{
    public function getRankingPayload(int $schoolId, ?string $schoolName, array $filters = []): array
    {
        $normalizedFilters = $this->normalizeRankingFilters($filters);

        $players = DB::select('CALL sp_player_stats(?, ?, ?, ?, ?, ?)', [
            $schoolId,
            $normalizedFilters['year'],
            $normalizedFilters['position'],
            $normalizedFilters['player_id'],
            $normalizedFilters['category'],
            20,
        ]);

        $this->enrichPlayersWithPhotos($players);

        return [
            'players' => $players,
            'school' => [
                'id' => $schoolId,
                'name' => $schoolName,
            ],
            'positions' => collect($this->getPositions())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
            'categories' => $this->getCategories($schoolId)->values(),
            'filters' => [
                'position' => $normalizedFilters['position'],
                'player_id' => $normalizedFilters['player_id'],
                'category' => $normalizedFilters['category'],
            ],
        ];
    }

    public function getTopPlayersPayload(int $schoolId): array
    {
        $topScorers = $this->getTopScorers($schoolId);
        $topAssists = $this->getTopAssists($schoolId);
        $topGoalSaves = $this->getTopGoalSaves($schoolId);
        $topRated = $this->getTopRated($schoolId);

        $this->enrichPlayersWithPhotos($topScorers);
        $this->enrichPlayersWithPhotos($topAssists);
        $this->enrichPlayersWithPhotos($topGoalSaves);
        $this->enrichPlayersWithPhotos($topRated);

        return [
            'updated_at' => now()->toDateString(),
            'season' => now()->year,
            'top_scorers' => $topScorers,
            'top_assists' => $topAssists,
            'top_goal_saves' => $topGoalSaves,
            'top_rated' => $topRated,
        ];
    }

    public function getPlayerDetailPayload(int $playerId): ?array
    {
        $playerStats = $this->findPlayerStats($playerId);

        if (!$playerStats) {
            return null;
        }

        $playerStats->photo = $this->resolvePhotoUrl($playerStats->photo ?? null);

        return [
            'player' => $playerStats,
            'positions_history' => $this->getPositionsHistory($playerId),
            'recent_matches' => $this->getRecentMatches($playerId),
        ];
    }

    private function normalizeRankingFilters(array $filters): array
    {
        return [
            'year' => isset($filters['year']) && $filters['year'] !== '' ? (int) $filters['year'] : null,
            'position' => isset($filters['position']) && $filters['position'] !== '' ? $filters['position'] : null,
            'player_id' => isset($filters['player_id']) && $filters['player_id'] !== '' ? (int) $filters['player_id'] : null,
            'category' => isset($filters['category']) && $filters['category'] !== '' ? $filters['category'] : null,
        ];
    }

    private function getPositions(): array
    {
        return config('app.key_positions', [
            'Portero' => 'Portero',
            'Defensa(Central)' => 'Defensa(Central)',
            'Defensa(Derecho)(Izquierdo)' => 'Defensa(Derecho)(Izquierdo)',
            'Volante(Central)' => 'Volante(Central)',
            'Volante(Primera linea)' => 'Volante(Primera linea)',
            'Volante(Segunda linea)' => 'Volante(Segunda linea)',
            'Volante(Extremo)' => 'Volante(Extremo)',
            'Delantero' => 'Delantero',
        ]);
    }

    private function getCategories(int $schoolId)
    {
        return Cache::remember("KEY_CATEGORIES_SELECT_{$schoolId}_PLUCK", now()->addMinutes(5), function () use ($schoolId) {
            return DB::table('inscriptions')
                ->where('school_id', $schoolId)
                ->where('year', now()->year)
                ->orderBy('category')
                ->groupBy('category')
                ->pluck('category');
        });
    }

    private function getTopScorers(int $schoolId)
    {
        return $this->topPlayersBaseQuery($schoolId)
            ->selectRaw("
                i.player_id,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                p.photo,
                s.name as school_name,
                SUM(sc.goals) as total_goles,
                COUNT(sc.id) as partidos,
                SUM(sc.played_approx) as minutos_jugados,
                ROUND(
                    CASE
                        WHEN COUNT(sc.id) > 0
                        THEN SUM(sc.goals) / COUNT(sc.id)
                        ELSE 0
                    END, 2
                ) as promedio_goles,
                ROUND(AVG(
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+(\.[0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(3,2))
                        ELSE 0
                    END
                ), 2) as promedio_calificacion
            ")
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('total_goles', '>', 0)
            ->orderBy('total_goles', 'DESC')
            ->limit(10)
            ->get();
    }

    private function getTopAssists(int $schoolId)
    {
        return $this->topPlayersBaseQuery($schoolId)
            ->selectRaw("
                i.player_id,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                p.photo,
                s.name as school_name,
                SUM(sc.goal_assists) as total_asistencias,
                COUNT(sc.id) as partidos,
                ROUND(
                    CASE
                        WHEN COUNT(sc.id) > 0
                        THEN SUM(sc.goal_assists) / COUNT(sc.id)
                        ELSE 0
                    END, 2
                ) as promedio_asistencias
            ")
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('total_asistencias', '>', 0)
            ->orderBy('total_asistencias', 'DESC')
            ->limit(10)
            ->get();
    }

    private function getTopGoalSaves(int $schoolId)
    {
        return $this->topPlayersBaseQuery($schoolId)
            ->selectRaw("
                i.player_id,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                p.photo,
                s.name as school_name,
                SUM(sc.goal_saves) as total_atajadas,
                COUNT(sc.id) as partidos,
                ROUND(AVG(
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+(\.[0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(3,2))
                        ELSE 0
                    END
                ), 2) as promedio_calificacion
            ")
            ->where('sc.position', 'like', '%Portero%')
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('total_atajadas', '>', 0)
            ->orderBy('total_atajadas', 'DESC')
            ->limit(10)
            ->get();
    }

    private function getTopRated(int $schoolId)
    {
        return $this->topPlayersBaseQuery($schoolId)
            ->selectRaw("
                i.player_id,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                p.photo,
                s.name as school_name,
                ROUND(AVG(
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+(\.[0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(3,2))
                        ELSE 0
                    END
                ), 2) as promedio_calificacion,
                COUNT(sc.id) as partidos,
                SUM(sc.goals) as goals,
                SUM(sc.goal_assists) as assists,
                SUM(sc.goal_saves) as saves
            ")
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('partidos', '>=', 3)
            ->having('promedio_calificacion', '>', 0)
            ->orderBy('promedio_calificacion', 'DESC')
            ->limit(9)
            ->get();
    }

    private function topPlayersBaseQuery(int $schoolId): Builder
    {
        return DB::table('skills_control as sc')
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->leftJoin('schools as s', 'sc.school_id', '=', 's.id')
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->where('sc.school_id', $schoolId);
    }

    private function findPlayerStats(int $playerId): ?object
    {
        return DB::table('skills_control as sc')
            ->selectRaw("
                i.player_id,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                p.photo,
                p.date_birth,
                COUNT(sc.id) as total_partidos,
                SUM(sc.assistance) as asistencias_partidos,
                SUM(sc.titular) as veces_titular,
                ROUND(AVG(
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+(\.[0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(3,2))
                        ELSE 0
                    END
                ), 2) as promedio_calificacion,
                SUM(sc.goals) as total_goles,
                SUM(sc.goal_assists) as total_asistencias_gol,
                SUM(sc.goal_saves) as total_atajadas,
                SUM(sc.yellow_cards) as total_amarillas,
                SUM(sc.red_cards) as total_rojas,
                SUM(sc.played_approx) as minutos_jugados,
                ROUND(
                    CASE
                        WHEN SUM(sc.assistance) > 0
                        THEN SUM(sc.played_approx) / SUM(sc.assistance)
                        ELSE 0
                    END, 2
                ) as promedio_minutos_partido,
                (
                    SUM(sc.goals) * 10 +
                    SUM(sc.goal_assists) * 7 +
                    SUM(sc.goal_saves) * 5 +
                    ROUND(AVG(
                        CASE
                            WHEN sc.qualification REGEXP '^[0-9]+(\.[0-9]+)?$'
                            THEN CAST(sc.qualification AS DECIMAL(3,2))
                            ELSE 0
                        END
                    ), 2) * 3 +
                    (SUM(sc.played_approx) * 0.1) +
                    SUM(CASE WHEN sc.titular = 1 THEN 3 ELSE 0 END) -
                    SUM(sc.yellow_cards) * 2 -
                    SUM(sc.red_cards) * 5
                ) as puntaje_escalafon
            ")
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->where('i.player_id', $playerId)
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 'p.date_birth')
            ->first();
    }

    private function getPositionsHistory(int $playerId)
    {
        return DB::table('skills_control as sc')
            ->selectRaw("
                sc.position,
                COUNT(*) as veces_jugada,
                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as porcentaje
            ")
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->where('i.player_id', $playerId)
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->whereNotNull('sc.position')
            ->groupBy('sc.position')
            ->orderBy('veces_jugada', 'DESC')
            ->get();
    }

    private function getRecentMatches(int $playerId)
    {
        return DB::table('skills_control as sc')
            ->select([
                'g.date as fecha_partido',
                'sc.position',
                'sc.played_approx as minutos',
                'sc.goals',
                'sc.goal_assists',
                'sc.goal_saves',
                'sc.yellow_cards',
                'sc.red_cards',
                DB::raw("
                    CASE
                        WHEN sc.qualification REGEXP '^[0-9]+(\.[0-9]+)?$'
                        THEN CAST(sc.qualification AS DECIMAL(3,2))
                        ELSE 0
                    END as qualification
                "),
                'sc.observation',
            ])
            ->join('games as g', 'sc.game_id', '=', 'g.id')
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->where('i.player_id', $playerId)
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->orderBy('g.date', 'DESC')
            ->limit(10)
            ->get();
    }

    private function enrichPlayersWithPhotos(iterable $players): void
    {
        foreach ($players as $player) {
            $player->photo = $this->resolvePhotoUrl($player->photo ?? null);
        }
    }

    private function resolvePhotoUrl(?string $photo): string
    {
        if (!empty($photo) && Storage::disk('public')->exists($photo)) {
            return route('images', $photo);
        }

        return url('img/user.png');
    }
}
