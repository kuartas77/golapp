<?php

namespace App\Http\Requests\API;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page' => ['required'],
            'per_page' => ['required'],
            'skip' => ['required'],
            'sort_by' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        $page = ($this->page && $this->page > 0) ? $this->page : 1;
        $per_page = ($this->per_page > 0) ? $this->per_page : 15;

        $this->merge([
            'page' => $page,
            'per_page' => $per_page,
            'skip' => ($per_page * ($page - 1))
        ]);
    }
}
