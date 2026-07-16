<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\PlayerCreditMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayerCreditMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([PlayerCreditMovement::TYPE_CREDIT, PlayerCreditMovement::TYPE_DEBIT])],
            'amount' => ['required', 'integer', 'min:1'],
            'movement_date' => ['required', 'date'],
            'concept' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => (int) preg_replace('/[^0-9]/', '', (string) $this->input('amount')),
        ]);
    }
}
