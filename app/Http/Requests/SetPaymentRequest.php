<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetPaymentRequest extends FormRequest
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
            'january' => ['required'],
            'february' => ['required'],
            'march' => ['required'],
            'april' => ['required'],
            'may' => ['required'],
            'june' => ['required'],
            'july' => ['required'],
            'august' => ['required'],
            'september' => ['required'],
            'october' => ['required'],
            'november' => ['required'],
            'december' => ['required'],
            'enrollment' => ['required'],
            'enrollment_amount' => ['required'],
            'january_amount' => ['required'],
            'february_amount' => ['required'],
            'march_amount' => ['required'],
            'april_amount' => ['required'],
            'may_amount' => ['required'],
            'june_amount' => ['required'],
            'july_amount' => ['required'],
            'august_amount' => ['required'],
            'september_amount' => ['required'],
            'october_amount' => ['required'],
            'november_amount' => ['required'],
            'december_amount' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'enrollment_amount' => $this->cleanString($this->enrollment_amount),
            'january_amount' => $this->cleanString($this->january_amount),
            'february_amount' => $this->cleanString($this->february_amount),
            'march_amount' => $this->cleanString($this->march_amount),
            'april_amount' => $this->cleanString($this->april_amount),
            'may_amount' => $this->cleanString($this->may_amount),
            'june_amount' => $this->cleanString($this->june_amount),
            'july_amount' => $this->cleanString($this->july_amount),
            'august_amount' => $this->cleanString($this->august_amount),
            'september_amount' => $this->cleanString($this->september_amount),
            'october_amount' => $this->cleanString($this->october_amount),
            'november_amount' => $this->cleanString($this->november_amount),
            'december_amount' => $this->cleanString($this->december_amount),
        ]);
    }

    private function cleanString($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
