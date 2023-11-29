<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetTournamentPaymentRequest extends FormRequest
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
            'status' => ['required'],
            'value' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'value' => $this->cleanString($this->value),
        ]);
    }

    private function cleanString($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
