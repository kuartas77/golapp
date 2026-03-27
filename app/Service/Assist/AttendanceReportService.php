<?php

namespace App\Service\Assist;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    public function monthlyByPlayerQuery(array $filters): Builder
    {
        return DB::table('vw_assists_detail as d')
            ->join('inscriptions as i', 'i.id', '=', 'd.inscription_id')
            ->join('players as p', 'p.id', '=', 'i.player_id')
            ->join('training_groups as tg', 'tg.id', '=', 'd.training_group_id')
            ->selectRaw("
                d.school_id,
                d.training_group_id,
                tg.name as training_group_name,
                d.inscription_id,
                p.id as player_id,
                p.unique_code,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                d.year,
                d.month,
                SUM(CASE WHEN d.status_id = 1 THEN 1 ELSE 0 END) as total_asistencias,
                SUM(CASE WHEN d.status_id = 2 THEN 1 ELSE 0 END) as total_faltas,
                SUM(CASE WHEN d.status_id = 3 THEN 1 ELSE 0 END) as total_excusas,
                SUM(CASE WHEN d.status_id = 4 THEN 1 ELSE 0 END) as total_retiros,
                SUM(CASE WHEN d.status_id = 5 THEN 1 ELSE 0 END) as total_incapacidades,
                COUNT(*) as total_sesiones_registradas,
                ROUND(
                    SUM(CASE WHEN d.status_id = 1 THEN 1 ELSE 0 END) * 100 / NULLIF(COUNT(*), 0),
                    2
                ) as porcentaje_asistencia
            ")
            ->where('d.year', $filters['year'])
            ->where('d.month', $filters['month'])
            ->when($filters['school_id'] ?? null, fn ($q, $schoolId) => $q->where('d.school_id', $schoolId))
            ->when($filters['training_group_id'] ?? null, fn ($q, $groupId) => $q->where('d.training_group_id', $groupId))
            ->groupBy(
                'd.school_id',
                'd.training_group_id',
                'tg.name',
                'd.inscription_id',
                'p.id',
                'p.unique_code',
                'p.names',
                'p.last_names',
                'd.year',
                'd.month'
            );
    }

    public function monthlyByGroupQuery(array $filters): Builder
    {
        return DB::table('vw_assists_detail as d')
            ->join('training_groups as tg', 'tg.id', '=', 'd.training_group_id')
            ->selectRaw("
                d.school_id,
                d.training_group_id,
                tg.name as training_group_name,
                d.year,
                d.month,
                COUNT(DISTINCT d.inscription_id) as total_jugadores,
                SUM(CASE WHEN d.status_id = 1 THEN 1 ELSE 0 END) as total_asistencias,
                SUM(CASE WHEN d.status_id = 2 THEN 1 ELSE 0 END) as total_faltas,
                SUM(CASE WHEN d.status_id = 3 THEN 1 ELSE 0 END) as total_excusas,
                SUM(CASE WHEN d.status_id = 4 THEN 1 ELSE 0 END) as total_retiros,
                SUM(CASE WHEN d.status_id = 5 THEN 1 ELSE 0 END) as total_incapacidades,
                COUNT(*) as total_sesiones_registradas,
                ROUND(
                    SUM(CASE WHEN d.status_id = 1 THEN 1 ELSE 0 END) * 100 / NULLIF(COUNT(*), 0),
                    2
                ) as porcentaje_asistencia
            ")
            ->where('d.year', $filters['year'])
            ->where('d.month', $filters['month'])
            ->when($filters['school_id'] ?? null, fn ($q, $schoolId) => $q->where('d.school_id', $schoolId))
            ->when($filters['training_group_id'] ?? null, fn ($q, $groupId) => $q->where('d.training_group_id', $groupId))
            ->groupBy(
                'd.school_id',
                'd.training_group_id',
                'tg.name',
                'd.year',
                'd.month'
            );
    }

    /**
     * Reporte anual consolidado por jugador.
     */
    public function annualConsolidatedQuery(array $filters): Builder
    {
        return DB::table('vw_assists_detail as d')
            ->join('inscriptions as i', 'i.id', '=', 'd.inscription_id')
            ->join('players as p', 'p.id', '=', 'i.player_id')
            ->join('training_groups as tg', 'tg.id', '=', 'd.training_group_id')
            ->selectRaw("
                d.school_id,
                d.training_group_id,
                tg.name as training_group_name,
                d.inscription_id,
                p.id as player_id,
                p.unique_code,
                CONCAT(p.names, ' ', p.last_names) as player_name,
                d.year,
                SUM(CASE WHEN d.status_id = 1 THEN 1 ELSE 0 END) as total_asistencias,
                SUM(CASE WHEN d.status_id = 2 THEN 1 ELSE 0 END) as total_faltas,
                SUM(CASE WHEN d.status_id = 3 THEN 1 ELSE 0 END) as total_excusas,
                SUM(CASE WHEN d.status_id = 4 THEN 1 ELSE 0 END) as total_retiros,
                SUM(CASE WHEN d.status_id = 5 THEN 1 ELSE 0 END) as total_incapacidades,
                COUNT(*) as total_sesiones_registradas,
                ROUND(
                    SUM(CASE WHEN d.status_id = 1 THEN 1 ELSE 0 END) * 100 / NULLIF(COUNT(*), 0),
                    2
                ) as porcentaje_asistencia
            ")
            ->where('d.year', $filters['year'])
            ->when($filters['school_id'] ?? null, fn ($q, $schoolId) => $q->where('d.school_id', $schoolId))
            ->when($filters['training_group_id'] ?? null, fn ($q, $groupId) => $q->where('d.training_group_id', $groupId))
            ->when($filters['inscription_id'] ?? null, fn ($q, $inscriptionId) => $q->where('d.inscription_id', $inscriptionId))
            ->groupBy(
                'd.school_id',
                'd.training_group_id',
                'tg.name',
                'd.inscription_id',
                'p.id',
                'p.unique_code',
                'p.names',
                'p.last_names',
                'd.year'
            );
    }
}