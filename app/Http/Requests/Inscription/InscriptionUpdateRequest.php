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
            'player_id' => ['required', 'numeric', 'bail'],
            'school_id' => ['required', 'numeric', 'bail'],
            'unique_code' => ['required', 'exists:players,unique_code', 'bail'],
            'year' => ['required', 'bail'],
            'start_date' => ['required', 'bail'],
            'category' => ['required', 'bail'],
            'training_group_id' => ['nullable', 'numeric'],
            'competition_groups' => ['nullable', 'array'],
            'photos' => ['nullable', 'boolean'],
            'copy_identification_document' => ['nullable', 'boolean'],
            'eps_certificate' => ['nullable', 'boolean'],
            'medic_certificate' => ['nullable', 'boolean'],
            'study_certificate' => ['nullable', 'boolean'],
            'overalls' => ['nullable', 'boolean'],
            'ball' => ['nullable', 'boolean'],
            'bag' => ['nullable', 'boolean'],
            'presentation_uniform' => ['nullable', 'boolean'],
            'competition_uniform' => ['nullable', 'boolean'],
            'tournament_pay' => ['nullable', 'boolean'],
            'period_one' => ['nullable'],
            'period_two' => ['nullable'],
            'period_three' => ['nullable'],
            'period_four' => ['nullable'],
            'scholarship' => ['nullable', 'boolean'],
            'pre_inscription' => ['nullable', 'boolean'],
        ];
    }

    /**
     *
     */
    protected function prepareForValidation(): void
    {
        $dateBirth = Player::find($this->player_id)->date_birth;
        $startDate = Date::parse($this->start_date);
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'year' => $startDate->year,
            'start_date' => $startDate,
            'category' => categoriesName(Date::parse($dateBirth)->year),
            'photos' => $this->input('photos', false),
            'copy_identification_document' => $this->input('copy_identification_document', false),
            'eps_certificate' => $this->input('eps_certificate', false),
            'medic_certificate' => $this->input('medic_certificate', false),
            'study_certificate' => $this->input('study_certificate', false),
            'overalls' => $this->input('overalls', false),
            'ball' => $this->input('ball', false),
            'bag' => $this->input('bag', false),
            'presentation_uniform' => $this->input('presentation_uniform', false),
            'competition_uniform' => $this->input('competition_uniform', false),
            'tournament_pay' => $this->input('tournament_pay', false),
            'scholarship' => $this->input('scholarship', false),
            'competition_groups' => array_filter($this->input('competition_groups', [])),
            'training_group_id' => $this->filled('training_group_id') ? $this->training_group_id : null,
            'pre_inscription' => $this->input('pre_inscription', false),
        ]);
    }
}
