<?php

namespace App\Http\Requests;

use App\Models\Setting;
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
        return isSchool() || isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $groupPricingEnabled = (bool) getSchool(auth()->user())->training_group_monthly_payment_enabled;
        $legacyOptionRules = $groupPricingEnabled
            ? ['prohibited']
            : ['required', 'string'];

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
            'MONTHLY_PAYMENT_OPTION_1' => $legacyOptionRules,
            'MONTHLY_PAYMENT_OPTION_2' => $legacyOptionRules,
            'MONTHLY_PAYMENT_OPTION_3' => $legacyOptionRules,
            'ANNUITY' => ['required', 'string'],
            'create_contract' => ['prohibited'],
            'send_debt_notifications' => ['prohibited'],
            'send_documents' => ['prohibited'],
            'send_monthly_payment_receipts' => ['prohibited'],
            'training_group_monthly_payment_enabled' => ['prohibited'],
            'tutor_platform' => ['prohibited'],
            'sign_player' => ['prohibited'],
            'inscriptions_enabled' => ['prohibited'],
            Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED => ['prohibited'],
            Setting::CATEGORY_FORMAT => ['prohibited'],
        ];
    }

    protected function prepareForValidation()
    {
        $data = [
            'NOTIFY_PAYMENT_DAY' => $this->cleanString($this->NOTIFY_PAYMENT_DAY),
            'INSCRIPTION_AMOUNT' => $this->cleanString($this->INSCRIPTION_AMOUNT),
            'MONTHLY_PAYMENT' => $this->cleanString($this->MONTHLY_PAYMENT),
            'BROTHER_MONTHLY_PAYMENT' => $this->cleanString($this->BROTHER_MONTHLY_PAYMENT),
            'ANNUITY' => $this->cleanString($this->ANNUITY),
            'logo' => $this->hasFile('logo') ? $this->logo : null,
        ];

        if (! getSchool(auth()->user())->training_group_monthly_payment_enabled) {
            $data += [
                'MONTHLY_PAYMENT_OPTION_1' => $this->cleanString($this->MONTHLY_PAYMENT_OPTION_1),
                'MONTHLY_PAYMENT_OPTION_2' => $this->cleanString($this->MONTHLY_PAYMENT_OPTION_2),
                'MONTHLY_PAYMENT_OPTION_3' => $this->cleanString($this->MONTHLY_PAYMENT_OPTION_3),
            ];
        }

        $this->merge($data);
    }

    private function cleanString($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}
