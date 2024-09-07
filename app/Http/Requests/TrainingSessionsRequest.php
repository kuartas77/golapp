<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TrainingSessionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => ['nullable', 'numeric'],
            'school_id' => ['required','numeric'],
            'user_id' => ['required', 'numeric'],
            'training_group_id' => ['required', 'numeric'],
            'year' => ['required'],

            "period" => ['nullable', 'string'],
            "session" => ['nullable', 'string'],
            "date" => ['nullable', 'string'],
            "hour" => ['nullable', 'string'],
            "training_ground" => ['nullable', 'string'],
            "material" => ['nullable', 'string'],
            "back_to_calm" => ['nullable', 'numeric'],
            "players" => ['nullable', 'string'],
            "absences" => ['nullable', 'string'],
            "incidents" => ['nullable', 'string'],
            "feedback" => ['nullable', 'string'],
            "warm_up" => ['nullable', 'string'],
            "coaches" => ['nullable', 'string'],

            "task_number" => ['required', 'array', 'min:3'],
            "task_number.*" => ['numeric'],
            "task_name" => ['required', 'array', 'min:3'],
            "task_name.*" => ['nullable', 'string'],
            "general_objective" => ['required', 'array', 'min:3'],
            "general_objective.*" => ['nullable', 'string'],
            "specific_goal" => ['required', 'array', 'min:3'],
            "specific_goal.*" => ['nullable', 'string'],
            "content_one" => ['required', 'array', 'min:3'],
            "content_one.*" => ['nullable', 'string'],
            "content_two" => ['required', 'array', 'min:3'],
            "content_two.*" => ['nullable', 'string'],
            "content_three" => ['required', 'array', 'min:3'],
            "content_three.*" => ['nullable', 'string'],
            "ts" => ['required', 'array', 'min:3'],
            "ts.*" => ['nullable', 'string'],
            "sr" => ['required', 'array', 'min:3'],
            "sr.*" => ['nullable', 'string'],
            "tt" => ['required', 'array', 'min:3'],
            "tt.*" => ['nullable', 'string'],
            "observations" => ['required', 'array', 'min:3'],
            "observations.*" => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation()
    {
        $date = ($this->date ?? now()->format('Y-m-d'));
        $hour = ($this->hour ?? now()->format('h:i A'));
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'user_id' => auth()->id(),
            'date' => ($this->date ?? $date),
            'hour' => ($this->hour ?? $hour),
            'year' => Carbon::parse($date)->year,
        ]);
    }
}
