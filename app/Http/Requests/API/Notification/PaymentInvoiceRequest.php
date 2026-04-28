<?php

namespace App\Http\Requests\API\Notification;

use App\Models\Player;
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
        return $this->user() instanceof Player;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
            'payment_method' => ['required', 'string'],
            'image' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'payment_method' => Str::upper($this->payment_method),
        ]);
    }
}
