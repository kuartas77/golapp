<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AttendanceQrTakeRequest;
use App\Models\Assist;
use App\Models\Inscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AttendanceQrController extends Controller
{
    public function show(string $uniqueCode): JsonResponse
    {
        $now = now();

        $inscription = Inscription::query()
            ->schoolId()
            ->where('unique_code', $uniqueCode)
            ->where('year', $now->year)
            ->with([
                'player',
                'trainingGroup' => fn ($query) => $query->withTrashed(),
            ])
            ->first();

        if (!$inscription) {
            return response()->json([
                'message' => 'No encontramos una inscripción vigente para este código en el año actual.',
            ], 404);
        }

        if (!$inscription->trainingGroup) {
            return response()->json([
                'message' => 'La inscripción no tiene un grupo de entrenamiento disponible.',
            ], 422);
        }

        abort_if(
            isInstructor() && !instructorCanAccessTrainingGroup($inscription->training_group_id, $now->year),
            403,
            'No tienes acceso al grupo de entrenamiento de este deportista.'
        );

        $assist = Assist::query()
            ->schoolId()
            ->where('inscription_id', $inscription->id)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->first();

        if (!$assist) {
            return response()->json([
                'message' => 'No existe un registro de asistencia disponible para el mes actual.',
            ], 404);
        }

        $classDays = $this->buildClassDays(
            assist: $assist,
            days: array_map('dayToNumber', $inscription->trainingGroup->explode_days)
        );

        return response()->json([
            'player' => [
                'id' => $inscription->player?->id,
                'full_names' => $inscription->player?->full_names,
                'names' => $inscription->player?->names,
                'last_names' => $inscription->player?->last_names,
                'photo_url' => $inscription->player
                    ? str_replace('img/dynamic', 'api/img/dynamic', $inscription->player->photo_url)
                    : null,
            ],
            'unique_code' => $inscription->unique_code,
            'inscription_id' => $inscription->id,
            'assist_id' => $assist->id,
            'training_group' => [
                'id' => $inscription->trainingGroup->id,
                'name' => $inscription->trainingGroup->name,
                'full_group' => $inscription->trainingGroup->full_group,
            ],
            'year' => $assist->year,
            'month' => (int) $assist->getRawOriginal('month'),
            'month_name' => getMonth((int) $assist->getRawOriginal('month')),
            'class_days' => $classDays->values()->all(),
            'current_values' => $classDays
                ->mapWithKeys(fn (array $classDay) => [$classDay['column'] => $classDay['current_value']])
                ->all(),
        ]);
    }

    public function take(AttendanceQrTakeRequest $request, int $assist): JsonResponse
    {
        $assistModel = Assist::query()
            ->schoolId()
            ->with([
                'trainingGroup' => fn ($query) => $query->withTrashed(),
                'inscription:id,player_id,unique_code,training_group_id,year',
                'inscription.player:id,names,last_names,unique_code',
            ])
            ->find($assist);

        if (!$assistModel) {
            return response()->json([
                'message' => 'No encontramos el registro de asistencia solicitado.',
            ], 404);
        }

        abort_if(
            isInstructor() && !instructorCanAccessTrainingGroup($assistModel->training_group_id, (int) $assistModel->year),
            403,
            'No tienes acceso al grupo de entrenamiento de este deportista.'
        );

        if ((int) $assistModel->year !== now()->year || (int) $assistModel->getRawOriginal('month') !== now()->month) {
            return response()->json([
                'message' => 'La toma rápida de asistencia sólo permite registrar clases del mes actual.',
            ], 422);
        }

        if (!$assistModel->trainingGroup) {
            return response()->json([
                'message' => 'El grupo de entrenamiento asociado ya no está disponible.',
            ], 422);
        }

        $allowedColumns = $this->buildClassDays(
            assist: $assistModel,
            days: array_map('dayToNumber', $assistModel->trainingGroup->explode_days)
        )->pluck('column');

        if (!$allowedColumns->contains($request->string('column')->value())) {
            return response()->json([
                'message' => 'La clase seleccionada no corresponde a los días válidos del mes actual.',
            ], 422);
        }

        $column = $request->string('column')->value();
        $assistModel->{$column} = 1;
        $assistModel->save();

        return response()->json([
            'saved' => true,
            'assist_id' => $assistModel->id,
            'column' => $column,
            'current_value' => 1,
        ]);
    }

    private function buildClassDays(Assist $assist, array $days): Collection
    {
        return classDays((int) $assist->year, (int) $assist->getRawOriginal('month'), $days)
            ->sortBy('date')
            ->values()
            ->map(function (array $classDay) use ($assist) {
                $column = $classDay['column'];
                $currentValue = data_get($assist, $column);

                return [
                    'label' => sprintf(
                        '#%s · %s %s',
                        $classDay['number_class'],
                        Str::ucfirst($classDay['name']),
                        $classDay['day']
                    ),
                    'date' => $classDay['date'],
                    'day' => Str::ucfirst($classDay['name']),
                    'column' => $column,
                    'is_today' => $classDay['date'] === now()->toDateString(),
                    'current_value' => $currentValue,
                    'current_label' => $currentValue ? (config('variables.KEY_ASSIST')[$currentValue] ?? 'Registrado') : null,
                ];
            });
    }
}
