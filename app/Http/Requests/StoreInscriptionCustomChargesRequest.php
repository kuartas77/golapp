<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInscriptionCustomChargesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool() || isAssistant();
    }

    public function rules(): array
    {
        $schoolId = getSchool($this->user())->id;

        return [
            'charges' => ['required', 'array', 'min:1'],
            'charges.*.invoice_custom_item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('invoice_custom_items', 'id')->where('school_id', $schoolId),
            ],
            'charges.*.value' => ['required', 'numeric', 'gt:0'],
            'charges.*.due_date' => ['required', 'date'],
        ];
    }
}
