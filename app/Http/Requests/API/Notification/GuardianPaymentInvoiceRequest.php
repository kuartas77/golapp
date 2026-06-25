<?php

namespace App\Http\Requests\API\Notification;

use App\Models\People;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardianPaymentInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof People;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'card', 'transfer', 'check', 'other'])],
            'image' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_method' => Str::lower($this->payment_method),
        ]);
    }
}
