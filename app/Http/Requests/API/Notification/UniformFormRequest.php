<?php

namespace App\Http\Requests\API\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UniformFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
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
            'quantity' => ['required', 'numeric'],
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
