<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationStoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_title' => ['required', 'string'],
            'notification_body' => ['required', 'string'],
            'notification_type' => ['required', 'string', 'in:general,categories,training_groups,competition_groups,players'],
            'categories'          => ['nullable', 'array', 'min:1'],
            'training_groups'     => ['nullable', 'array', 'min:1'],
            'competition_groups'  => ['nullable', 'array', 'min:1'],
            'players'             => ['nullable', 'array', 'min:1'],
            'school_id'             => ['required', 'numeric'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes(
            ['categories', 'training_groups', 'competition_groups', 'players'],
            'required_without_all:categories,training_groups,competition_groups,players',
            function ($input) {
                return ($input->notification_type ?? null) !== 'general';
            }
        );
    }

    public function messages()
    {
        return [
            'categories.required_without_all' => 'Debes enviar al menos uno: categorias, grupos de entrenamiento, grupos de competencia o jugadores.',
            'training_groups.required_without_all' => 'Debes enviar al menos uno: categorias, grupos de entrenamiento, grupos de competencia o jugadores.',
            'competition_groups.required_without_all' => 'Debes enviar al menos uno: categorias, grupos de entrenamiento, grupos de competencia o jugadores.',
            'players.required_without_all' => 'Debes enviar al menos uno: categorias, grupos de entrenamiento, grupos de competencia o jugadores.',
            '*.min' => 'Cada lista debe contener al menos 1 elemento.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
