<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class PlayerCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'unique_code' => ['required'],
            'names' => ['required'],
            'last_names' => ['required'],
            'gender' => ['required'],
            'date_birth' => ['required'],
            'place_birth' => ['required'],
            'identification_document' => ['required'],
            'rh' => ['nullable'],
            'eps' => ['required'],
            'email' => ['required'],
            'address' => ['required'],
            'municipality' => ['required'],
            'neighborhood' => ['required'],
            'zone' => ['nullable'],
            'commune' => ['nullable'],
            'phones' => ['required'],
            'mobile' => ['required'],
            'school' => ['required'],
            'degree' => ['required'],
            'player' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'position_field' => ['nullable'],
            'dominant_profile' => ['nullable'],

            'people'=> 'array',
            'people.*.relationship',
            'people.*.names',
            'people.*.phone',
            'people.*.mobile',
            'people.*.identification_card',
            'people.*.neighborhood',
            'people.*.email',
            'people.*.profession',
            'people.*.business',
            'people.*.position',
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => auth()->user()->school->id
        ]);
    }
}
