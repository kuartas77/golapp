<?php

namespace App\Http\Requests\API\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PaymentInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'invoice_id' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
            'payment_method' => ['required', 'string'],
            'image' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'payment_method' => Str::upper($this->payment_method),
        ]);
    }
}
