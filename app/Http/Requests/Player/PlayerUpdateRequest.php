<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class PlayerUpdateRequest extends FormRequest
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
            'names' => 'required',
            'last_names' => 'required',
            'gender' => 'required',
            'date_birth' => 'required',
            'place_birth' => 'required',
            'identification_document' => 'required',
            'rh' => 'required',
            'eps' => 'required',
            'email' => 'required',
            'address' => 'required',
            'municipality' => 'required',
            'neighborhood' => 'required',
            'zone' => 'nullable',
            'commune' => 'nullable',
            'phones' => 'required',
            'mobile' => 'required',
            'school' => 'required',
            'degree' => 'required',
            'image' => 'nullable|file',

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
