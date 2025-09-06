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
        $validation = [
            'january' => ['nullable'],
            'february' => ['nullable'],
            'march' => ['nullable'],
            'april' => ['nullable'],
            'may' => ['nullable'],
            'june' => ['nullable'],
            'july' => ['nullable'],
            'august' => ['nullable'],
            'september' => ['nullable'],
            'october' => ['nullable'],
            'november' => ['nullable'],
            'december' => ['nullable'],
            'enrollment' => ['nullable'],
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

        if ($this->filled('column')) {
            $validation['enrollment_amount'] = ['nullable'];
            $validation['january_amount'] = ['nullable'];
            $validation['february_amount'] = ['nullable'];
            $validation['march_amount'] = ['nullable'];
            $validation['april_amount'] = ['nullable'];
            $validation['may_amount'] = ['nullable'];
            $validation['june_amount'] = ['nullable'];
            $validation['july_amount'] = ['nullable'];
            $validation['august_amount'] = ['nullable'];
            $validation['september_amount'] = ['nullable'];
            $validation['october_amount'] = ['nullable'];
            $validation['november_amount'] = ['nullable'];
            $validation['december_amount'] = ['nullable'];
        }

        return $validation;
    }

    protected function prepareForValidation()
    {
        if (!$this->filled('column')) {
            $this->merge([
                'january' => ($this->january ?? 0),
                'february' => ($this->february ?? 0),
                'march' => ($this->march ?? 0),
                'april' => ($this->april ?? 0),
                'may' => ($this->may ?? 0),
                'june' => ($this->june ?? 0),
                'july' => ($this->july ?? 0),
                'august' => ($this->august ?? 0),
                'september' => ($this->september ?? 0),
                'october' => ($this->october ?? 0),
                'november' => ($this->november ?? 0),
                'december' => ($this->december ?? 0),
                'enrollment' => ($this->enrollment ?? 0),

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
    }

    private function cleanString($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
