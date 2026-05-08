<?php

namespace App\Http\Requests\API\Notification;

use App\Models\People;
use Illuminate\Foundation\Http\FormRequest;

class GuardianUniformFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof People;
    }

    public function rules(): array
    {
        return [
            'player_id' => ['required', 'integer'],
            'type' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['required', 'string'],
            'additional_notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'additional_notes' => $this->additionalNotes ?? $this->additional_notes,
        ]);
    }
}
