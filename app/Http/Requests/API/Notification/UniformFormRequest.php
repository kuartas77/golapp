<?php

namespace App\Http\Requests\API\Notification;

use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;

class UniformFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() instanceof Player;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['required', 'string'],
            'additional_notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'additional_notes' => $this->additionalNotes,
        ]);
    }
}
