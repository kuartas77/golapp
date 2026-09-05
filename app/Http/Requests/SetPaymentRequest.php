<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return isAdmin() || isSchool() || isAssistant();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $validation = [
            'column' => [isAssistant() ? 'required' : 'nullable', 'string', Rule::in(Payment::paymentFields())],
        ];

        foreach (Payment::FIELD_AMOUNT_MAP as $field => $amountField) {
            $statusRules = ['nullable', 'integer', Rule::in(Payment::STATUS_VALUES)];
            $amountRules = ['nullable', 'integer', 'min:0'];

            if (isAssistant() && $this->input('column') !== $field) {
                $validation[$field] = ['prohibited'];
                $validation[$amountField] = ['prohibited'];

                continue;
            }

            if (! $this->filled('column')) {
                $amountRules[0] = 'required';
            }

            if ($this->input('column') === $field) {
                $statusRules[0] = 'required';
            }

            if (isAssistant() && $this->input('column') === $field) {
                $statusRules[] = Rule::in([
                    Payment::$paid,
                    Payment::$paid_cash,
                    Payment::$paid_deposit,
                    Payment::$paid_,
                    Payment::$disability,
                ]);
                $amountRules = ['required', 'integer', 'min:1'];
            }

            $validation[$field] = $statusRules;
            $validation[$amountField] = $amountRules;
        }

        return $validation;
    }

    protected function prepareForValidation(): void
    {
        $mergedValues = [];

        if ($this->filled('column')) {
            $column = (string) $this->input('column');
            $amountField = Payment::amountFieldFor($column);

            if ($amountField && $this->has($amountField)) {
                $mergedValues[$amountField] = $this->cleanString($this->input($amountField));
            }

            if ($this->has($column) && $this->input($column) !== '') {
                $mergedValues[$column] = (int) $this->input($column);
            }

            $this->merge($mergedValues);

            return;
        }

        foreach (Payment::FIELD_AMOUNT_MAP as $field => $amountField) {
            $mergedValues[$field] = (int) $this->input($field, 0);
            $mergedValues[$amountField] = $this->cleanString($this->input($amountField));
        }

        $this->merge($mergedValues);
    }

    private function cleanString($value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }
}
