<?php

declare(strict_types=1);

namespace App\Service\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AttendancePaymentReportService
{
    public function monthlyByPlayerQuery(array $filters): Builder
    {
        return DB::table('vw_attendance_payment_report_detail as r')
            ->leftJoin('inscriptions as i', 'i.id', '=', 'r.inscription_id')
            ->leftJoin('players as pl', 'pl.id', '=', 'i.player_id')
            ->leftJoin('training_groups as tg', 'tg.id', '=', 'r.training_group_id')
            ->selectRaw("
                r.school_id,
                r.training_group_id,
                COALESCE(tg.name, 'Sin grupo') AS training_group_name,
                r.inscription_id,
                pl.id AS player_id,
                COALESCE(pl.unique_code, i.unique_code) AS unique_code,
                {$this->playerNameExpression()} AS player_name,
                r.year,
                r.month,
                r.total_attendances,
                r.total_sessions_registered,
                r.has_attendance,
                r.payment_status_code,
                r.payment_status_label,
                r.is_flagged,
                r.flag_reason
            ")
            ->where('r.school_id', $filters['school_id'])
            ->where('r.year', $filters['year'])
            ->where('r.month', $filters['month'])
            ->when(
                $filters['training_group_id'] ?? null,
                fn (Builder $query, $groupId) => $query->where('r.training_group_id', $groupId)
            )
            ->where('r.is_flagged', 1)
            ->orderBy('tg.name')
            ->orderBy('pl.names')
            ->orderBy('pl.last_names');
    }

    public function monthlyByGroupQuery(array $filters): Builder
    {
        return DB::table('vw_attendance_payment_report_detail as r')
            ->leftJoin('training_groups as tg', 'tg.id', '=', 'r.training_group_id')
            ->selectRaw('
                r.school_id,
                r.training_group_id,
                COALESCE(tg.name, \'Sin grupo\') AS training_group_name,
                r.year,
                r.month,
                SUM(CASE WHEN r.has_attendance = 1 THEN 1 ELSE 0 END) AS players_with_attendance,
                SUM(CASE WHEN r.is_flagged = 1 THEN 1 ELSE 0 END) AS flagged_players,
                SUM(r.total_attendances) AS total_attendances,
                ROUND(
                    100.0 * SUM(CASE WHEN r.is_flagged = 1 THEN 1 ELSE 0 END)
                    / NULLIF(SUM(CASE WHEN r.has_attendance = 1 THEN 1 ELSE 0 END), 0),
                    2
                ) AS flagged_percentage
            ')
            ->where('r.school_id', $filters['school_id'])
            ->where('r.year', $filters['year'])
            ->where('r.month', $filters['month'])
            ->when(
                $filters['training_group_id'] ?? null,
                fn (Builder $query, $groupId) => $query->where('r.training_group_id', $groupId)
            )
            ->groupBy(
                'r.school_id',
                'r.training_group_id',
                'tg.name',
                'r.year',
                'r.month'
            )
            ->havingRaw('SUM(CASE WHEN r.has_attendance = 1 THEN 1 ELSE 0 END) > 0')
            ->orderBy('tg.name');
    }

    private function playerNameExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "TRIM(COALESCE(pl.names, '') || ' ' || COALESCE(pl.last_names, ''))";
        }

        return "TRIM(CONCAT(COALESCE(pl.names, ''), ' ', COALESCE(pl.last_names, '')))";
    }
}
