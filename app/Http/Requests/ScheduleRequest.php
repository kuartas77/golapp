<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
            'schedule_start' => ['required', 'string'],
            'schedule_end' => ['required', 'string'],
            'schedule' => ['required', 'string'],
            'school_id' => ['required'],
        ];
    }

    protected function prepareForValidation()
    {
        $scheduleStart = Str::upper((string) preg_replace('/\s+/', '', trim((string) $this->input('schedule_start'))));
        $scheduleEnd = Str::upper((string) preg_replace('/\s+/', '', trim((string) $this->input('schedule_end'))));

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'schedule_start' => $scheduleStart,
            'schedule_end' => $scheduleEnd,
            'schedule' => sprintf('%s - %s', $scheduleStart, $scheduleEnd),
        ]);
    }
}
