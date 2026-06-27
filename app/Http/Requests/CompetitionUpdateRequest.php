<?php

namespace App\Http\Requests;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompetitionUpdateRequest extends FormRequest
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
            'date' => [
                'required',
                'date',
                Rule::when($this->input('status') === Game::STATUS_PLAYED, ['before_or_equal:today']),
            ],
            'hour' => ['required'],
            'num_match' => ['required'],
            'place' => ['required'],
            'rival_name' => ['required'],
            'status' => ['required', Rule::in(Game::STATUSES)],
            'final_score' => ['nullable', 'array', Rule::requiredIf($this->input('status') === Game::STATUS_PLAYED)],
            'final_score.soccer' => ['nullable', 'integer', 'min:0', Rule::requiredIf($this->input('status') === Game::STATUS_PLAYED)],
            'final_score.rival' => ['nullable', 'integer', 'min:0', Rule::requiredIf($this->input('status') === Game::STATUS_PLAYED)],
            'general_concept' => ['nullable'],
            'school_id' => ['required'],
            'skill_controls' => ['nullable', 'array', Rule::requiredIf($this->input('status') === Game::STATUS_PLAYED)],
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
            'skill_controls.*.qualification' => ['required', 'numeric'],
            'skill_controls.*.observation' => ['nullable', 'string'],
        ];

        if ($this->isMethod('put')) {
            $rules['skill_controls.*.game_id'] = ['required', 'numeric'];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $schoolScore = $this->input('final_score_school');
        $rivalScore = $this->input('final_score_rival');
        $finalScore = ($schoolScore === null || $schoolScore === '') && ($rivalScore === null || $rivalScore === '')
            ? null
            : ['soccer' => $schoolScore, 'rival' => $rivalScore];
        $match = $this->route('match');
        $gameId = is_object($match) ? $match->id : $match;
        $status = $this->input('status', is_object($match) ? $match->status : Game::STATUS_SCHEDULED);
        $skillControls = collect($this->input('skill_controls', []))
            ->map(function ($skillControl) use ($gameId) {
                $skillControl['game_id'] = $gameId;

                return $skillControl;
            })
            ->all();

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'status' => $status,
            'final_score' => $finalScore,
            'skill_controls' => $skillControls,
        ]);
    }
}
