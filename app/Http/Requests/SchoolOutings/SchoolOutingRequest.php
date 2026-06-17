<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolOutings;

use Illuminate\Foundation\Http\FormRequest;

class SchoolOutingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date'],
            'amount_per_player' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'amount_per_player' => $this->normalizeNumber($this->input('amount_per_player')),
            'notes' => blank($this->input('notes')) ? null : $this->input('notes'),
        ]);
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if (is_string($value)) {
            return preg_replace('/[^0-9.]/', '', $value);
        }

        return $value;
    }
}
