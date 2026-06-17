<?php

declare(strict_types=1);

namespace App\Service\Reports;

use App\Models\Assist;
use App\Models\MethodologyRecord;
use App\Models\TrainingGroup;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InstructorActivityReportService
{
    public function query(array $filters): Builder
    {
        $schoolId = (int) $filters['school_id'];
        $year = (int) $filters['year'];
        $month = (int) $filters['month'];
        $instructorId = isset($filters['instructor_id']) ? (int) $filters['instructor_id'] : null;
        [$startDate, $endDate] = $this->monthRange($year, $month);

        $matches = DB::table('games')
            ->join('competition_groups as cg', 'cg.id', '=', 'games.competition_group_id')
            ->selectRaw('cg.user_id as instructor_id, COUNT(DISTINCT games.id) as matches_count')
            ->where('cg.school_id', $schoolId)
            ->whereBetween('games.date', [$startDate, $endDate])
            ->whereNull('games.deleted_at')
            ->groupBy('cg.user_id');

        $trainingSessions = DB::table('training_sessions')
            ->selectRaw('user_id as instructor_id, COUNT(*) as training_sessions_count')
            ->where('school_id', $schoolId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->groupBy('user_id');

        $methodologyRecords = DB::table('methodology_records')
            ->selectRaw('
                user_id as instructor_id,
                COUNT(*) as methodology_total,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as methodology_planning_count,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as methodology_characterization_count,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as methodology_monthly_count,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as methodology_category_monthly_count
            ', [
                MethodologyRecord::TYPE_PLANNING,
                MethodologyRecord::TYPE_CHARACTERIZATION_SHEET,
                MethodologyRecord::TYPE_MONTHLY_REPORT,
                MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT,
            ])
            ->where('school_id', $schoolId)
            ->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ])
            ->whereNull('deleted_at')
            ->groupBy('user_id');

        return DB::table('users as instructors')
            ->join('schools_user', 'schools_user.user_id', '=', 'instructors.id')
            ->join('model_has_roles', function ($join): void {
                $join->on('model_has_roles.model_id', '=', 'instructors.id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftJoinSub($matches, 'matches_report', 'matches_report.instructor_id', '=', 'instructors.id')
            ->leftJoinSub($trainingSessions, 'training_sessions_report', 'training_sessions_report.instructor_id', '=', 'instructors.id')
            ->leftJoinSub($methodologyRecords, 'methodology_report', 'methodology_report.instructor_id', '=', 'instructors.id')
            ->where('schools_user.school_id', $schoolId)
            ->where('roles.name', 'instructor')
            ->whereNull('instructors.deleted_at')
            ->when($instructorId, fn (Builder $query) => $query->where('instructors.id', $instructorId))
            ->selectRaw('
                instructors.id as instructor_id,
                instructors.name as instructor_name,
                ? as year,
                ? as month,
                COALESCE(matches_report.matches_count, 0) as matches_count,
                COALESCE(training_sessions_report.training_sessions_count, 0) as training_sessions_count,
                COALESCE(methodology_report.methodology_total, 0) as methodology_total,
                COALESCE(methodology_report.methodology_planning_count, 0) as methodology_planning_count,
                COALESCE(methodology_report.methodology_characterization_count, 0) as methodology_characterization_count,
                COALESCE(methodology_report.methodology_monthly_count, 0) as methodology_monthly_count,
                COALESCE(methodology_report.methodology_category_monthly_count, 0) as methodology_category_monthly_count
            ', [$year, $month])
            ->distinct();
    }

    public function rows(array $filters): Collection
    {
        $attendanceCoverage = $this->attendanceCoverage($filters);

        return $this->query($filters)
            ->orderBy('instructor_name')
            ->get()
            ->map(function ($row) use ($attendanceCoverage) {
                $coverage = $attendanceCoverage[(int) $row->instructor_id] ?? [
                    'taken' => 0,
                    'pending' => 0,
                ];

                $row->attendances_taken_count = $coverage['taken'];
                $row->attendances_pending_count = $coverage['pending'];
                $row->attendances_expected_count = $coverage['taken'] + $coverage['pending'];
                $row->attendance_coverage = "{$coverage['taken']}/{$row->attendances_expected_count}";
                $row->month_label = $this->monthLabel((int) $row->month);

                return $row;
            });
    }

    public function exportRows(array $filters): array
    {
        return $this->rows($filters)
            ->map(fn ($row) => [
                'Instructor' => $row->instructor_name,
                'Año' => $row->year,
                'Mes' => $row->month_label,
                'Asistencias tomadas/por tomar' => $row->attendance_coverage,
                'Partidos registrados' => $row->matches_count,
                'Sesiones de entrenamiento' => $row->training_sessions_count,
                'Metodologías total' => $row->methodology_total,
                'Planeaciones' => $row->methodology_planning_count,
                'Caracterizaciones' => $row->methodology_characterization_count,
                'Informes mensuales' => $row->methodology_monthly_count,
                'Informes por categoría' => $row->methodology_category_monthly_count,
            ])
            ->values()
            ->all();
    }

    public function years(int $schoolId): Collection
    {
        $years = collect([now()->year]);
        $gameYearExpression = $this->yearExpression('date');
        $methodologyYearExpression = $this->yearExpression('created_at');

        $years = $years
            ->merge(DB::table('assists')->where('school_id', $schoolId)->distinct()->pluck('year'))
            ->merge(
                DB::table('games')
                    ->join('competition_groups as cg', 'cg.id', '=', 'games.competition_group_id')
                    ->where('cg.school_id', $schoolId)
                    ->selectRaw("{$gameYearExpression} as year")
                    ->distinct()
                    ->pluck('year')
            )
            ->merge(DB::table('methodology_records')->where('school_id', $schoolId)->selectRaw("{$methodologyYearExpression} as year")->distinct()->pluck('year'))
            ->merge(DB::table('training_sessions')->where('school_id', $schoolId)->distinct()->pluck('year'));

        return $years
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
    }

    public function instructorOptions(int $schoolId): Collection
    {
        return DB::table('users')
            ->join('schools_user', 'schools_user.user_id', '=', 'users.id')
            ->join('model_has_roles', function ($join): void {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('schools_user.school_id', $schoolId)
            ->where('roles.name', 'instructor')
            ->whereNull('users.deleted_at')
            ->orderBy('users.name')
            ->select('users.id', 'users.name')
            ->distinct()
            ->get()
            ->map(fn ($user) => [
                'value' => (int) $user->id,
                'label' => $user->name,
            ])
            ->values();
    }

    public function monthLabel(int $month): string
    {
        return (string) (config('variables.KEY_MONTHS_INDEX')[$month] ?? $month);
    }

    private function monthRange(int $year, int $month): array
    {
        $start = CarbonImmutable::create($year, $month, 1);

        return [
            $start->toDateString(),
            $start->endOfMonth()->toDateString(),
        ];
    }

    private function yearExpression(string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "CAST(strftime('%Y', {$column}) AS INTEGER)";
        }

        return "YEAR({$column})";
    }

    private function attendanceCoverage(array $filters): array
    {
        $schoolId = (int) $filters['school_id'];
        $year = (int) $filters['year'];
        $month = (int) $filters['month'];
        $instructorId = isset($filters['instructor_id']) ? (int) $filters['instructor_id'] : null;
        $coverage = [];

        $assignedGroups = TrainingGroup::query()
            ->select('training_groups.*', 'training_group_user.user_id as instructor_id')
            ->join('training_group_user', 'training_group_user.training_group_id', '=', 'training_groups.id')
            ->where('training_groups.school_id', $schoolId)
            ->where('training_group_user.assigned_year', $year)
            ->when($instructorId, fn ($query) => $query->where('training_group_user.user_id', $instructorId))
            ->get();

        foreach ($assignedGroups as $group) {
            $instructorKey = (int) $group->instructor_id;
            $coverage[$instructorKey] ??= [
                'taken' => 0,
                'pending' => 0,
            ];

            $columns = classDays(
                $year,
                $month,
                array_map('dayToNumber', $this->groupDays($group))
            )->pluck('column')->values();

            if ($columns->isEmpty()) {
                continue;
            }

            $activeInscriptions = DB::table('inscriptions')
                ->where('school_id', $schoolId)
                ->where('training_group_id', $group->id)
                ->where('year', $year)
                ->whereNull('deleted_at')
                ->count();

            $expected = $activeInscriptions * $columns->count();
            $taken = $this->takenAttendancesForGroup((int) $group->id, $schoolId, $year, $month, $columns);

            $coverage[$instructorKey]['taken'] += $taken;
            $coverage[$instructorKey]['pending'] += max($expected - $taken, 0);
        }

        return $coverage;
    }

    private function takenAttendancesForGroup(
        int $trainingGroupId,
        int $schoolId,
        int $year,
        int $month,
        Collection $columns
    ): int {
        $select = $columns
            ->map(fn (string $column) => "SUM(CASE WHEN {$column} IS NOT NULL THEN 1 ELSE 0 END)")
            ->implode(' + ');

        return (int) (Assist::query()
            ->where('school_id', $schoolId)
            ->where('training_group_id', $trainingGroupId)
            ->where('year', $year)
            ->where('month', $month)
            ->selectRaw("COALESCE({$select}, 0) as taken_count")
            ->value('taken_count') ?? 0);
    }

    private function groupDays(TrainingGroup $group): array
    {
        if (blank($group->days)) {
            return [];
        }

        return collect(explode(',', (string) $group->days))
            ->map(fn (string $day) => trim($day))
            ->filter()
            ->values()
            ->all();
    }
}
