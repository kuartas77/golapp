<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (isSchool() || isAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'agent' => ['required', 'string'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'NOTIFY_PAYMENT_DAY' => ['required', 'string'],
            'INSCRIPTION_AMOUNT' => ['required', 'string'],
            'MONTHLY_PAYMENT' => ['required', 'string'],
            'ANNUITY' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'NOTIFY_PAYMENT_DAY' => $this->cleanString($this->NOTIFY_PAYMENT_DAY),
            'INSCRIPTION_AMOUNT' => $this->cleanString($this->INSCRIPTION_AMOUNT),
            'MONTHLY_PAYMENT' => $this->cleanString($this->MONTHLY_PAYMENT),
            'ANNUITY' => $this->cleanString($this->ANNUITY),
            'logo' => $this->hasFile('logo') ? $this->logo : null,
        ]);
    }

    private function cleanString($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
