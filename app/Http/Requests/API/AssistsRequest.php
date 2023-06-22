<?php

namespace App\Http\Requests\API;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AssistsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isInstructor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $months = config('variables.KEY_MONTHS_INDEX');
        return [
            'group_id' => ['required', 'numeric'],
            'month' => ['required', Rule::in($months)],
            'year' => ['nullable', 'numeric'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'month' => Str::title($this->month),
            'year' => $this->year ?? now()->year
        ]);
    }
}
