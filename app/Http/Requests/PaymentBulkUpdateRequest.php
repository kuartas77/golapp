<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['required', 'integer', 'distinct'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'string', Rule::in(Payment::paymentFields())],
            'status' => ['required', 'integer', Rule::in(Payment::STATUS_VALUES)],
            'amount' => ['nullable', 'integer', 'min:0'],
            'school_id' => ['required', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'status' => (int) $this->input('status'),
            'amount' => $this->cleanString($this->input('amount')),
        ]);
    }

    private function cleanString($value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }
}
