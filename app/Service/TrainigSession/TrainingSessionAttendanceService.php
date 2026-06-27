<?php

declare(strict_types=1);

namespace App\Service\TrainigSession;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Service\Kpi\KpiCacheService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class TrainingSessionAttendanceService
{
    private const PROTECTED_STATUSES = [3, 4, 5];

    public function context(TrainingGroup $group, string $date): array
    {
        $classDay = $this->resolveClassDay($group, $date);
        $inscriptions = $this->eligibleInscriptions($group, Carbon::parse($date))->get();
        $assists = $this->assistsFor($group, Carbon::parse($date), $inscriptions->pluck('id'))->get()
            ->keyBy('inscription_id');

        $players = [];
        $protectedPlayers = [];
        $currentAbsenceIds = [];
        $attendanceCount = 0;

        foreach ($inscriptions as $inscription) {
            $status = data_get($assists->get($inscription->id), $classDay['column']);
            $player = [
                'value' => (int) $inscription->id,
                'label' => $inscription->player?->full_names ?? "Inscripción #{$inscription->id}",
                'unique_code' => $inscription->player?->unique_code,
                'status' => $status === null ? null : (int) $status,
            ];

            if (in_array((int) $status, self::PROTECTED_STATUSES, true)) {
                $player['status_label'] = config('variables.KEY_ASSIST')[(int) $status] ?? 'Estado protegido';
                $protectedPlayers[] = $player;
                continue;
            }

            if ((int) $status === 2) {
                $currentAbsenceIds[] = (int) $inscription->id;
            } elseif ((int) $status === 1) {
                $attendanceCount++;
            }

            $players[] = $player;
        }

        return [
            'date' => Carbon::parse($date)->toDateString(),
            'column' => $classDay['column'],
            'players' => $players,
            'protected_players' => $protectedPlayers,
            'current_absence_ids' => $currentAbsenceIds,
            'attendance_count' => $attendanceCount,
            'eligible_count' => $inscriptions->count(),
        ];
    }

    public function sync(TrainingSession $session, TrainingGroup $group, array $absenceInscriptionIds): void
    {
        $date = Carbon::parse($session->date);

        if ($date->year !== (int) now()->year) {
            throw ValidationException::withMessages([
                'date' => 'La sincronización de asistencias solo está disponible para el año actual.',
            ]);
        }

        $classDay = $this->resolveClassDay($group, $date->toDateString());
        $inscriptions = $this->eligibleInscriptions($group, $date)->lockForUpdate()->get();
        $eligibleIds = $inscriptions->pluck('id')->map(fn ($id) => (int) $id)->values();
        $requestedIds = collect($absenceInscriptionIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($requestedIds->diff($eligibleIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'absence_inscription_ids' => 'La selección contiene deportistas que no pertenecen al grupo o no están activos para esta fecha.',
            ]);
        }

        foreach ($eligibleIds as $inscriptionId) {
            Assist::query()->updateOrCreate([
                'training_group_id' => $group->id,
                'inscription_id' => $inscriptionId,
                'year' => $date->year,
                'month' => $date->month,
                'school_id' => $group->school_id,
            ]);
        }

        $assists = $this->assistsFor($group, $date, $eligibleIds)->lockForUpdate()->get();
        $protectedIds = $assists
            ->filter(fn (Assist $assist) => in_array((int) $assist->{$classDay['column']}, self::PROTECTED_STATUSES, true))
            ->pluck('inscription_id')
            ->map(fn ($id) => (int) $id);

        if ($requestedIds->intersect($protectedIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'absence_inscription_ids' => 'No puedes marcar como falta un deportista con Excusa, Retiro o Incapacidad.',
            ]);
        }

        $modifiableIds = $eligibleIds->diff($protectedIds)->values();
        $absenceIds = $requestedIds->intersect($modifiableIds)->values();
        $attendanceIds = $modifiableIds->diff($absenceIds)->values();

        if ($absenceIds->isNotEmpty()) {
            $this->assistsFor($group, $date, $absenceIds)->update([$classDay['column'] => 2]);
        }

        if ($attendanceIds->isNotEmpty()) {
            $this->assistsFor($group, $date, $attendanceIds)->update([$classDay['column'] => 1]);
        }

        $session->absence_inscription_ids = $absenceIds->all();
        $session->players = $this->assistsFor($group, $date, $eligibleIds)
            ->where($classDay['column'], 1)
            ->count();
        $session->attendance_synced_at = now();
        $session->save();

        Cache::delete('statistics.groups.user.'.auth()->id());
        app(KpiCacheService::class)->invalidateSchool((int) $group->school_id);
    }

    public function absenceNames(TrainingSession $session): array
    {
        $ids = collect($session->absence_inscription_ids)->map(fn ($id) => (int) $id)->filter()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Inscription::query()
            ->withTrashed()
            ->with('player:id,names,last_names')
            ->where('school_id', $session->school_id)
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Inscription $inscription) => $ids->search((int) $inscription->id))
            ->map(fn (Inscription $inscription) => $inscription->player?->full_names)
            ->filter()
            ->values()
            ->all();
    }

    private function resolveClassDay(TrainingGroup $group, string $date): array
    {
        $carbonDate = Carbon::parse($date);
        $classDay = classDays(
            $carbonDate->year,
            $carbonDate->month,
            array_map('dayToNumber', $group->explode_days)
        )->firstWhere('date', $carbonDate->toDateString());

        if (! $classDay) {
            throw ValidationException::withMessages([
                'date' => 'La fecha seleccionada no corresponde a un día de entrenamiento del grupo.',
            ]);
        }

        return $classDay;
    }

    private function eligibleInscriptions(TrainingGroup $group, Carbon $date)
    {
        return Inscription::query()
            ->schoolId()
            ->with('player:id,names,last_names,unique_code')
            ->where('training_group_id', $group->id)
            ->where('year', $date->year)
            ->where('pre_inscription', false)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->orderBy('id');
    }

    private function assistsFor(TrainingGroup $group, Carbon $date, Collection $inscriptionIds)
    {
        return Assist::query()
            ->where('school_id', $group->school_id)
            ->where('training_group_id', $group->id)
            ->where('year', $date->year)
            ->where('month', $date->month)
            ->whereIn('inscription_id', $inscriptionIds);
    }
}
