<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceAddPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|gt:0',
            'idempotency_key' => 'nullable|string|max:64',
            'payment_method' => 'required|in:cash,card,transfer,check,other',
            'issue_date' => 'required|date',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:100',
            'paid_items' => 'required|array|min:1',
            'paid_items.*' => 'required|integer|distinct',
            'school_id' => 'required',
        ];
    }

    protected function prepareForValidation()
    {
        $amount = $this->input('amount');
        $cleanedValue = is_numeric($amount)
            ? $amount
            : preg_replace('/[^0-9]/', '', (string) $amount);

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'amount' => $cleanedValue,
        ]);
    }
}
