<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceCustomItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
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
            'name' => ['required', 'string'],
            'unit_price' => ['required', 'numeric', 'min:1'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'type' => $this->item_type,
            'name' => $this->item_name,
            'unit_price' => $this->cleanString($this->item_unit_price),
        ]);
    }

    private function cleanString($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
