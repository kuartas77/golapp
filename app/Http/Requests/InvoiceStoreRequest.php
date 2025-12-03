<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inscription_id' => 'required|exists:inscriptions,id',
            'training_group_id' => 'required|exists:training_groups,id',
            'year' => 'required|digits:4',
            'due_date' => 'required|date',
            'student_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:monthly,enrollment,additional',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.month' => 'nullable|string',
            'items.*.payment_id' => 'nullable|exists:payments,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
