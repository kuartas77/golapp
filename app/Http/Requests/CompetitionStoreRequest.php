<?php

namespace App\Http\Requests;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;

class CompetitionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
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
            'status' => ['required', 'in:'.implode(',', Game::STATUSES)],
            'final_score' => ['nullable', 'array'],
            'general_concept' => ['nullable'],
            'school_id' => ['required'],
            'skill_controls' => ['nullable', 'array'],
            'skill_controls.*.id' => ['nullable'],
            'skill_controls.*.inscription_id' => ['required'],
            'skill_controls.*.assistance' => ['required', 'numeric'],
            'skill_controls.*.titular' => ['required', 'numeric'],
            'skill_controls.*.played_approx' => ['required', 'numeric'],
            'skill_controls.*.position' => ['nullable', 'string'],
            'skill_controls.*.goals' => ['required', 'numeric'],
            'skill_controls.*.goal_assists' => ['required', 'numeric'],
            'skill_controls.*.goal_saves' => ['required', 'numeric'],
            'skill_controls.*.red_cards' => ['required', 'numeric'],
            'skill_controls.*.yellow_cards' => ['required', 'numeric'],
            'skill_controls.*.qualification' => ['required', 'integer', 'between:1,5'],
            'skill_controls.*.observation' => ['nullable'],
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'status' => Game::STATUS_SCHEDULED,
            'final_score' => null,
        ]);
    }
}
