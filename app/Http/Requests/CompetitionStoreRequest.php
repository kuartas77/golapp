<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'id' => ['nullable', 'numeric'],
            'tournament_id' => ['required', 'numeric'],
            'competition_group_id' => ['required', 'numeric'],
            'date' => ['required'],
            'hour' => ['required'],
            'num_match' => ['required'],
            'place' => ['required'],
            'rival_name' => ['required'],
            'final_score' => ['required', 'array'],
            'general_concept' => ['nullable'],
            'school_id' => ['required'],
            'skill_controls' => ['required','array', 'min:1'],
            'skill_controls.*.id' => ['nullable'],
            'skill_controls.*.inscription_id' => ['required'],
            'skill_controls.*.assistance' => ['required', 'numeric'],
            'skill_controls.*.titular' => ['required', 'numeric'],
            'skill_controls.*.played_approx' => ['required', 'numeric'],
            'skill_controls.*.position' => ['required', 'string'],
            'skill_controls.*.goals' => ['required', 'numeric'],
            'skill_controls.*.goal_assists' => ['required', 'numeric'],
            'skill_controls.*.goal_saves' => ['required', 'numeric'],
            'skill_controls.*.red_cards' => ['required', 'numeric'],
            'skill_controls.*.yellow_cards' => ['required', 'numeric'],
            'skill_controls.*.qualification' => ['required', 'numeric'],
            'skill_controls.*.observation' => ['nullable'],
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $final_score = [];
        $final_score['soccer'] = $this->input('final_score_school');
        $final_score['rival'] = $this->input('final_score_rival');

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'final_score' => $final_score,
        ]);
    }
}
