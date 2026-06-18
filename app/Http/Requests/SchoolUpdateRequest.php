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
            'BROTHER_MONTHLY_PAYMENT' => ['required', 'string'],
            'MONTHLY_PAYMENT_OPTION_1' => ['required', 'string'],
            'MONTHLY_PAYMENT_OPTION_2' => ['required', 'string'],
            'MONTHLY_PAYMENT_OPTION_3' => ['required', 'string'],
            'ANNUITY' => ['required', 'string'],
            'create_contract' => ['nullable', 'boolean'],
            'send_documents' => ['nullable', 'boolean'],
            'send_monthly_payment_receipts' => ['nullable', 'boolean'],
            'tutor_platform' => ['nullable', 'boolean'],
            'sign_player' => ['nullable', 'boolean'],
            'inscriptions_enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $data = [
            'NOTIFY_PAYMENT_DAY' => $this->cleanString($this->NOTIFY_PAYMENT_DAY),
            'INSCRIPTION_AMOUNT' => $this->cleanString($this->INSCRIPTION_AMOUNT),
            'MONTHLY_PAYMENT' => $this->cleanString($this->MONTHLY_PAYMENT),
            'BROTHER_MONTHLY_PAYMENT' => $this->cleanString($this->BROTHER_MONTHLY_PAYMENT),
            'MONTHLY_PAYMENT_OPTION_1' => $this->cleanString($this->MONTHLY_PAYMENT_OPTION_1),
            'MONTHLY_PAYMENT_OPTION_2' => $this->cleanString($this->MONTHLY_PAYMENT_OPTION_2),
            'MONTHLY_PAYMENT_OPTION_3' => $this->cleanString($this->MONTHLY_PAYMENT_OPTION_3),
            'ANNUITY' => $this->cleanString($this->ANNUITY),
            'logo' => $this->hasFile('logo') ? $this->logo : null,
        ];

        foreach ([
            'create_contract',
            'send_documents',
            'send_monthly_payment_receipts',
            'tutor_platform',
            'sign_player',
            'inscriptions_enabled',
        ] as $field) {
            if ($this->has($field)) {
                $data[$field] = $this->boolean($field);
            }
        }

        $this->merge($data);
    }

    private function cleanString($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }
}
