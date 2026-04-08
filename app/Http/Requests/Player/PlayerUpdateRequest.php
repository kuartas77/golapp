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
        $people = [];
        for ($i = 0; $i <= 2; $i++) {
            $people[$i]['tutor'] = $i === 0;
            $people[$i]['names'] = $this->{'names_' . $i} !== 'null' ? $this->{'names_' . $i} : null;
            $people[$i]['phone'] = $this->{'phone_' . $i} !== 'null' ? $this->{'phone_' . $i} : null;
            $people[$i]['business'] = $this->{'business_' . $i} !== 'null' ? $this->{'business_' . $i} : null;
            $people[$i]['identification_card'] = $this->{'document_' . $i} !== 'null' ? $this->{'document_' . $i} : null;
            $people[$i]['relationship'] = $this->{'relationship_' . $i} !== 'null' ? $this->{'relationship_' . $i} : null;

            if (blank($people[$i]['names']) && blank($people[$i]['phone']) && blank($people[$i]['identification_card']) && blank($people[$i]['phone'])) {
                unset($people[$i]);
            }
        }

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'people' => $people,
            'mobile' => $this->phones
        ]);
    }
}
