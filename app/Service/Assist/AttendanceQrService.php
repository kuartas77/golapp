<?php

declare(strict_types=1);

namespace App\Service\Assist;

use App\Models\Assist;
use App\Models\Inscription;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AttendanceQrService
{
    public function __construct(private InstructorPeriodEditPolicy $periodEditPolicy) {}

    public function context(string $uniqueCode): array
    {
        $now = now();
        $inscription = Inscription::query()->schoolId()->where('unique_code', $uniqueCode)->where('year', $now->year)
            ->with(['player', 'trainingGroup' => fn ($query) => $query->withTrashed()])->first();
        abort_if(!$inscription, 404, 'No encontramos una inscripción vigente para este código en el año actual.');
        abort_if(!$inscription->trainingGroup, 422, 'La inscripción no tiene un grupo de entrenamiento disponible.');
        $this->assertInstructorAccess($inscription->training_group_id, $now->year);

        $assist = Assist::query()->schoolId()->where('inscription_id', $inscription->id)
            ->where('year', $now->year)->where('month', $now->month)->first();
        abort_if(!$assist, 404, 'No existe un registro de asistencia disponible para el mes actual.');
        $classDays = $this->classDays($assist, array_map('dayToNumber', $inscription->trainingGroup->explode_days));

        return [
            'player' => [
                'id' => $inscription->player?->id,
                'full_names' => $inscription->player?->full_names,
                'names' => $inscription->player?->names,
                'last_names' => $inscription->player?->last_names,
                'photo_url' => $inscription->player ? str_replace('img/dynamic', 'api/img/dynamic', $inscription->player->photo_url) : null,
            ],
            'unique_code' => $inscription->unique_code,
            'inscription_id' => $inscription->id,
            'assist_id' => $assist->id,
            'training_group' => ['id' => $inscription->trainingGroup->id, 'name' => $inscription->trainingGroup->name, 'full_group' => $inscription->trainingGroup->full_group],
            'year' => $assist->year,
            'month' => (int) $assist->getRawOriginal('month'),
            'month_name' => getMonth((int) $assist->getRawOriginal('month')),
            'class_days' => $classDays->values()->all(),
            'current_values' => $classDays->mapWithKeys(fn (array $day) => [$day['column'] => $day['current_value']])->all(),
        ];
    }

    public function take(int $assistId, string $column): array
    {
        $assist = Assist::query()->schoolId()->with([
            'trainingGroup' => fn ($query) => $query->withTrashed(),
            'inscription:id,player_id,unique_code,training_group_id,year,deleted_at',
            'inscription.player:id,names,last_names,unique_code',
        ])->find($assistId);
        abort_if(!$assist, 404, 'No encontramos el registro de asistencia solicitado.');
        abort_if($assist->inscription?->trashed(), 422, 'La inscripción está retirada; reactívala antes de modificar pagos o asistencias.');
        $this->assertInstructorAccess($assist->training_group_id, (int) $assist->year);
        $this->periodEditPolicy->assertCanMutateYearMonth((int) $assist->year, (int) $assist->getRawOriginal('month'), 'assist');
        abort_if((int) $assist->year !== now()->year || (int) $assist->getRawOriginal('month') !== now()->month, 422, 'La toma rápida de asistencia sólo permite registrar clases del mes actual.');
        abort_if(!$assist->trainingGroup, 422, 'El grupo de entrenamiento asociado ya no está disponible.');
        $allowed = $this->classDays($assist, array_map('dayToNumber', $assist->trainingGroup->explode_days))->pluck('column');
        abort_unless($allowed->contains($column), 422, 'La clase seleccionada no corresponde a los días válidos del mes actual.');
        $assist->{$column} = 1;
        $assist->save();

        return ['saved' => true, 'assist_id' => $assist->id, 'column' => $column, 'current_value' => 1];
    }

    private function assertInstructorAccess(int $trainingGroupId, int $year): void
    {
        abort_if(isInstructor() && !instructorCanAccessTrainingGroup($trainingGroupId, $year), 403, 'No tienes acceso al grupo de entrenamiento de este deportista.');
    }

    private function classDays(Assist $assist, array $days): Collection
    {
        return classDays((int) $assist->year, (int) $assist->getRawOriginal('month'), $days)->sortBy('date')->values()
            ->map(function (array $day) use ($assist) {
                $column = $day['column'];
                $value = data_get($assist, $column);
                return [
                    'label' => sprintf('#%s · %s %s', $day['number_class'], Str::ucfirst($day['name']), $day['day']),
                    'date' => $day['date'], 'day' => Str::ucfirst($day['name']), 'column' => $column,
                    'is_today' => $day['date'] === now()->toDateString(), 'current_value' => $value,
                    'current_label' => $value ? (config('variables.KEY_ASSIST')[$value] ?? 'Registrado') : null,
                ];
            });
    }
}
