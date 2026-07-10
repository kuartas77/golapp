<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationStoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_title' => [
                'required',
                'string',
            ],

            'notification_body' => [
                'required',
                'string',
            ],

            'notification_type' => [
                'required',
                'string',
                Rule::in([
                    'general',
                    'categories',
                    'training_groups',
                    'competition_groups',
                    'players',
                ]),
            ],

            'categories' => [
                'exclude_unless:notification_type,categories',
                'required',
                'array',
                'min:1',
            ],

            'categories.*' => [
                'required',
                'string',
                'distinct',
            ],

            'training_groups' => [
                'exclude_unless:notification_type,training_groups',
                'required',
                'array',
                'min:1',
            ],

            'training_groups.*' => [
                'required',
                'string',
                'distinct',
            ],

            'competition_groups' => [
                'exclude_unless:notification_type,competition_groups',
                'required',
                'array',
                'min:1',
            ],

            'competition_groups.*' => [
                'required',
                'string',
                'distinct',
            ],

            'players' => [
                'exclude_unless:notification_type,players',
                'required',
                'array',
                'min:1',
            ],

            'players.*' => [
                'required',
                'integer',
                'distinct',
            ],

            'school_id' => [
                'required',
                'integer',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.required' =>
            'Debes seleccionar al menos una categoría.',

            'categories.min' =>
            'Debes seleccionar al menos una categoría.',

            'training_groups.required' =>
            'Debes seleccionar al menos un grupo de entrenamiento.',

            'training_groups.min' =>
            'Debes seleccionar al menos un grupo de entrenamiento.',

            'competition_groups.required' =>
            'Debes seleccionar al menos un grupo de competencia.',

            'competition_groups.min' =>
            'Debes seleccionar al menos un grupo de competencia.',

            'players.required' =>
            'Debes seleccionar al menos un jugador.',

            'players.min' =>
            'Debes seleccionar al menos un jugador.',

            'categories.*.integer' =>
            'Las categorías seleccionadas no son válidas.',

            'training_groups.*.integer' =>
            'Los grupos de entrenamiento seleccionados no son válidos.',

            'competition_groups.*.integer' =>
            'Los grupos de competencia seleccionados no son válidos.',

            'players.*.integer' =>
            'Los jugadores seleccionados no son válidos.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $players = $this->input('players');

        $this->merge([
            'school_id' => getSchool($this->user())->id,
            'players' => is_array($players)
                ? array_map(
                    fn ($playerId) => filter_var($playerId, FILTER_VALIDATE_INT) !== false
                        ? (int) $playerId
                        : $playerId,
                    $players
                )
                : $players,
        ]);
    }
}
