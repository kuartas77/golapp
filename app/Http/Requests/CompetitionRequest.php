<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionRequest extends FormRequest
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
        return [
            "ids" => ['array'],
            "name" => ['required', 'string'],
            "competition_group_id" => ['required', 'string'],
            "tournament_id" => ['required', 'string'],
            "user_id" => ['required', 'string'],
            "num_match" => ['required', 'string'],
            "place" => ['required', 'string'],
            "date" => ['required', 'string'],
            "hour" => ['required', 'string'],
            "rival_name" => ['required', 'string'],
            "final_score" => ['required', 'array'],
            "general_concept" => ['nullable', 'string'],
            "details" => ['nullable', 'string'],
            "inscriptions_id" => ['required', 'array'],
            "assistance" => ['required', 'array'],
            "titular" => ['required', 'array'],
            "played_approx" => ['required', 'array'],
            "position" => ['required', 'array'],
            "goals" => ['required', 'array'],
            "goal_assists" => ['nullable', 'array'],
            "goal_saves" => ['nullable', 'array'],
            "yellow_cards" => ['required', 'array'],
            "red_cards" => ['required', 'array'],
            "qualification" => ['required', 'array'],
            "observation" => ['required', 'array'],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id
        ]);
    }
}
