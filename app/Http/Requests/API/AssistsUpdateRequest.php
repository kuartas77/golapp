<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use App\Dto\DtoContract;
use App\Dto\AssistDTO;

class AssistsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return isSchool() || isInstructor() || isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable'],
            'school_id' => ['required', 'numeric'],
            'training_group_id' => ['required', 'numeric'],
            'inscription_id' => ['required', 'numeric'],
            'month' => ['required', 'numeric'],
            'year' => ['required', 'numeric'],
            'assistance_one' => ['nullable', 'string'],
            'assistance_two' => ['nullable', 'string'],
            'assistance_three' => ['nullable', 'string'],
            'assistance_four' => ['nullable', 'string'],
            'assistance_five' => ['nullable', 'string'],
            'assistance_six' => ['nullable', 'string'],
            'assistance_seven' => ['nullable', 'string'],
            'assistance_eight' => ['nullable', 'string'],
            'assistance_nine' => ['nullable', 'string'],
            'assistance_ten' => ['nullable', 'string'],
            'assistance_eleven' => ['nullable', 'string'],
            'assistance_twelve' => ['nullable', 'string'],
            'assistance_thirteen' => ['nullable', 'string'],
            'assistance_fourteen' => ['nullable', 'string'],
            'assistance_fifteen' => ['nullable', 'string'],
            'assistance_sixteen' => ['nullable', 'string'],
            'assistance_seventeen' => ['nullable', 'string'],
            'assistance_eighteen' => ['nullable', 'string'],
            'assistance_nineteen' => ['nullable', 'string'],
            'assistance_twenty' => ['nullable', 'string'],
            'assistance_twenty_one' => ['nullable', 'string'],
            'assistance_twenty_two' => ['nullable', 'string'],
            'assistance_twenty_three' => ['nullable', 'string'],
            'assistance_twenty_four' => ['nullable', 'string'],
            'assistance_twenty_five' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'attendance_date' => ['nullable', 'string'],
            'column' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric'],
        ];
    }

    protected function prepareForValidation(): void
    {

        $KEY_ASSIST = [
            'as' => 1,
            'fa' => 2,
            'ex' => 3,
            're' => 4,
            'in' => 5,
        ];

        $value = $KEY_ASSIST[$this->value];

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            $this->column => $value,
            'value' => $value,
            'training_group_id' => $this->group_id,
            'year' => $this->input('year', now()->year),
        ]);
    }

    public function toDto(): AssistDTO
    {
        return AssistDTO::fromArray($this->validated());
    }
}
