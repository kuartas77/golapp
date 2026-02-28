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
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,check,other',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
        ];
    }

    protected function prepareForValidation()
    {
        $cleanedValue = preg_replace('/[^0-9]/', '', $this->input('amount'));

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'amount' => $cleanedValue
        ]);
    }
}
