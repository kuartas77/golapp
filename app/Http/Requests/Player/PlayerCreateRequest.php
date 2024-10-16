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
        return isAdmin() || isSchool();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'unique_code' => ['nullable'],
            'names' => ['required'],
            'last_names' => ['required'],
            'gender' => ['required'],
            'date_birth' => ['required'],
            'place_birth' => ['required'],
            'identification_document' => ['required'],
            'rh' => ['nullable'],
            'eps' => ['required'],
            'email' => ['nullable', 'email'],
            'address' => ['required'],
            'municipality' => ['required'],
            'neighborhood' => ['required'],
            'zone' => ['nullable'],
            'commune' => ['nullable'],
            'phones' => ['required'],
            'mobile' => ['nullable'],
            'school' => ['required'],
            'degree' => ['nullable'],
            'player' => ['nullable', 'image', 'mimes:jpeg,png,jpg'],
            'position_field' => ['nullable'],
            'dominant_profile' => ['nullable'],

            'document_type' => ['nullable'],
            'medical_history' => ['nullable'],
            'jornada' => ['nullable'],
            'student_insurance' => ['nullable'],

            'people' => 'array',
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

    public function messages()
    {
        return [
            // 'unique_code' => 'El código ya fue registrado anteriormente.'
        ];
    }

    protected function prepareForValidation()
    {
        $people = $this->input('people', []);
        foreach ($people as $key => $person) {
            if ($person['relationship'] == '' && $person['names'] == '' && $person['identification_card'] == '' && $person['phone'] == '') {
                unset($people[$key]);
            }
        }

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'people' => array_values($people)
        ]);
    }
}
