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
            'assistance_one' => ['nullable', 'numeric'],
            'assistance_two' => ['nullable', 'numeric'],
            'assistance_three' => ['nullable', 'numeric'],
            'assistance_four' => ['nullable', 'numeric'],
            'assistance_five' => ['nullable', 'numeric'],
            'assistance_six' => ['nullable', 'numeric'],
            'assistance_seven' => ['nullable', 'numeric'],
            'assistance_eight' => ['nullable', 'numeric'],
            'assistance_nine' => ['nullable', 'numeric'],
            'assistance_ten' => ['nullable', 'numeric'],
            'assistance_eleven' => ['nullable', 'numeric'],
            'assistance_twelve' => ['nullable', 'numeric'],
            'assistance_thirteen' => ['nullable', 'numeric'],
            'assistance_fourteen' => ['nullable', 'numeric'],
            'assistance_fifteen' => ['nullable', 'numeric'],
            'assistance_sixteen' => ['nullable', 'numeric'],
            'assistance_seventeen' => ['nullable', 'numeric'],
            'assistance_eighteen' => ['nullable', 'numeric'],
            'assistance_nineteen' => ['nullable', 'numeric'],
            'assistance_twenty' => ['nullable', 'numeric'],
            'assistance_twenty_one' => ['nullable', 'numeric'],
            'assistance_twenty_two' => ['nullable', 'numeric'],
            'assistance_twenty_three' => ['nullable', 'numeric'],
            'assistance_twenty_four' => ['nullable', 'numeric'],
            'assistance_twenty_five' => ['nullable', 'numeric'],
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
