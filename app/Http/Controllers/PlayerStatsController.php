<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlayerStatsController extends Controller
{
    public function index(Request $request)
    {
        $school = getSchool(auth()->user());

        $query = DB::table('skills_control as sc')
            ->selectRaw("
                i.player_id,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                p.photo,
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
                ROUND(
                    CASE
                        WHEN SUM(sc.assistance) > 0
                        THEN SUM(sc.goals) / SUM(sc.assistance)
                        ELSE 0
                    END, 2
                ) as promedio_goles_partido,
                SUM(sc.yellow_cards) as total_amarillas,
                ROUND(AVG(sc.yellow_cards), 2) as promedio_amarillas_partido,
                SUM(sc.red_cards) as total_rojas,
                ROUND(AVG(sc.red_cards), 2) as promedio_rojas_partido,
                SUM(sc.played_approx) as minutos_jugados,
                ROUND(
                    CASE
                        WHEN SUM(sc.assistance) > 0
                        THEN SUM(sc.played_approx) / SUM(sc.assistance)
                        ELSE 0
                    END, 2
                ) as promedio_minutos_partido,
                SUM(sc.goal_assists) as total_asistencias_gol,
                SUM(sc.goal_saves) as total_atajadas,
                -- Calcular la posición principal (más frecuente)
                (
                    SELECT sc2.position
                    FROM skills_control sc2
                    JOIN inscriptions i2 ON sc2.inscription_id = i2.id
                    WHERE i2.player_id = i.player_id
                    AND sc2.position IS NOT NULL
                    AND sc2.deleted_at IS NULL
                    GROUP BY sc2.position
                    ORDER BY COUNT(*) DESC
                    LIMIT 1
                ) as posicion_principal,
                -- Calcular un puntaje para el escalafón (reglas ajustables)
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
                ) as puntaje_escalafon")
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1) // Solo partidos donde asistió
            ->where('sc.school_id', $school->id);


        // Filtros opcionales
        if ($request->filled('position')) {
            $query->where('sc.position', $request->position);
        }

        if ($request->filled('player_id')) {
            $query->where('i.player_id', $request->player_id);
        }

        $players = $query->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo')
            ->orderBy('puntaje_escalafon', 'DESC')
            ->limit(10)->get();

        // Obtener posiciones disponibles desde configuración
        $positions = config('app.key_positions', [
            'Portero' => 'Portero',
            'Defensa(Central)' => 'Defensa(Central)',
            'Defensa(Derecho)(Izquierdo)' => 'Defensa(Derecho)(Izquierdo)',
            'Volante(Central)' => 'Volante(Central)',
            'Volante(Primera linea)' => 'Volante(Primera linea)',
            'Volante(Segunda linea)' => 'Volante(Segunda linea)',
            'Volante(Extremo)' => 'Volante(Extremo)',
            'Delantero' => 'Delantero',
        ]);

        foreach ($players as $player) {
            if (!empty($player->photo) && Storage::disk('public')->exists($player->photo)) {
                $player->photo = route('images', $player->photo);
            } else {
                $player->photo = url('img/user.png');
            }
        }

        return view('player-stats.index', compact('players', 'school', 'positions'));
    }

    public function topPlayers()
    {
        // Top goleadores - incluir minutos jugados para calcular eficiencia
        $topScorers = DB::table('skills_control as sc')
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
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->leftJoin('schools as s', 'sc.school_id', '=', 's.id')
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('total_goles', '>', 0)
            ->orderBy('total_goles', 'DESC')
            ->limit(10)
            ->get();

        // Top asistencias
        $topAssists = DB::table('skills_control as sc')
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
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->leftJoin('schools as s', 'sc.school_id', '=', 's.id')
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('total_asistencias', '>', 0)
            ->orderBy('total_asistencias', 'DESC')
            ->limit(10)
            ->get();

        // Top porteros (atajadas)
        $topGoalSaves = DB::table('skills_control as sc')
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
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->leftJoin('schools as s', 'sc.school_id', '=', 's.id')
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->where('sc.position', 'like', '%Portero%')
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('total_atajadas', '>', 0)
            ->orderBy('total_atajadas', 'DESC')
            ->limit(10)
            ->get();

        // Jugadores mejor calificados (mínimo 3 partidos)
        $topRated = DB::table('skills_control as sc')
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
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->join('players as p', 'i.player_id', '=', 'p.id')
            ->leftJoin('schools as s', 'sc.school_id', '=', 's.id')
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->groupBy('i.player_id', 'p.names', 'p.last_names', 'p.photo', 's.name')
            ->having('partidos', '>=', 3) // Mínimo 3 partidos
            ->having('promedio_calificacion', '>', 0)
            ->orderBy('promedio_calificacion', 'DESC')
            ->limit(9) // Múltiplo de 3 para el grid
            ->get();

        foreach ($topScorers as $player) {
            if (!empty($player->photo) && Storage::disk('public')->exists($player->photo)) {
                $player->photo = route('images', $player->photo);
            } else {
                $player->photo = url('img/user.png');
            }
        }
        foreach ($topAssists as $player) {
            if (!empty($player->photo) && Storage::disk('public')->exists($player->photo)) {
                $player->photo = route('images', $player->photo);
            } else {
                $player->photo = url('img/user.png');
            }
        }

        foreach ($topGoalSaves as $player) {
            if (!empty($player->photo) && Storage::disk('public')->exists($player->photo)) {
                $player->photo = route('images', $player->photo);
            } else {
                $player->photo = url('img/user.png');
            }
        }
        foreach ($topRated as $player) {
            if (!empty($player->photo) && Storage::disk('public')->exists($player->photo)) {
                $player->photo = route('images', $player->photo);
            } else {
                $player->photo = url('img/user.png');
            }
        }

        return view('player-stats.top', compact(
            'topScorers',
            'topAssists',
            'topGoalSaves',
            'topRated'
        ));
    }

    public function playerDetail($playerId)
    {
        // Estadísticas generales del jugador
        $playerStats = DB::table('skills_control as sc')
            ->selectRaw("
            i.player_id,
            CONCAT(p.names, ' ', p.last_names) as player_name,
            p.photo,
            p.date_birth,
            COUNT(sc.id) as total_partidos,
            SUM(sc.assistance) as asistencias_partidos,
            SUM(sc.titular) as veces_titular,
            -- Calificación promedio (0-5)
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

            -- Calcular un puntaje para el escalafón (reglas ajustables)
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

        // Historial de posiciones jugadas
        $positionsHistory = DB::table('skills_control as sc')
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

        // Historial de partidos recientes
        $recentMatches = DB::table('skills_control as sc')
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
                'sc.observation'
            ])
            ->join('games as g', 'sc.game_id', '=', 'g.id')
            ->join('inscriptions as i', 'sc.inscription_id', '=', 'i.id')
            ->where('i.player_id', $playerId)
            ->whereNull('sc.deleted_at')
            ->where('sc.assistance', 1)
            ->orderBy('g.date', 'DESC')
            ->limit(10)
            ->get();

        if (!$playerStats) {
            abort(404, 'Jugador no encontrado o sin estadísticas');
        }

        if (!empty($playerStats->photo) && Storage::disk('public')->exists($playerStats->photo)) {
            $playerStats->photo = route('images', $player->photo);
        } else {
            $playerStats->photo = url('img/user.png');
        }

        return view('player-stats.detail', compact('playerStats', 'positionsHistory', 'recentMatches'));
    }
}
