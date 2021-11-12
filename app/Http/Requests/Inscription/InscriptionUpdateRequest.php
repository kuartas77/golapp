<?php

namespace App\Http\Requests\Inscription;

use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;
use Jenssegers\Date\Date;

class InscriptionUpdateRequest extends FormRequest
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
            'player_id' => 'required',
            'unique_code' => 'required',

            'training_group_id' => 'nullable',
            'competition_group_id' => 'nullable',

            'photos' => 'nullable',
            'copy_identification_document' => 'nullable',
            'eps_certificate' => 'nullable',
            'medic_certificate' => 'nullable',
            'study_certificate' => 'nullable',
            'overalls' => 'nullable',
            'ball' => 'nullable',
            'bag' => 'nullable',
            'presentation_uniform' => 'nullable',
            'competition_uniform' => 'nullable',
            'tournament_pay' => 'nullable',

            'period_one' => 'nullable',
            'period_two' => 'nullable',
            'period_three' => 'nullable',
            'period_four' => 'nullable',
        ];
    }

    /**
     *
     */
    protected function prepareForValidation()
    {
        $dateBirth = Player::find($this->player_id)->date_birth;
        $startDate = Date::parse($this->start_date);
        $this->merge([
            'year' => $startDate->year,
            'start_date' => $startDate,
            'category' => Date::parse($dateBirth)->year
        ]);
    }
}
