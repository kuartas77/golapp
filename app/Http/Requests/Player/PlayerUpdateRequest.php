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
            'people.*.tutor',
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
        $people = $this->input('people', []);
        foreach($people as $key => $person){
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
