<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
            'schedule' => ['required', 'string'],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'schedule' => sprintf("%s - %s", $this->schedule_start, $this->schedule_end)
        ]);
    }
}
