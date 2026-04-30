<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupAssignmentTrainingMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['super-admin', 'school']);
    }

    public function rules(): array
    {
        $schoolId = (int) getSchool($this->user())->id;

        return [
            'inscription_id' => [
                'required',
                'integer',
                Rule::exists('inscriptions', 'id')->where(
                    fn ($query) => $query->where('school_id', $schoolId)
                ),
            ],
            'target_group_id' => [
                'required',
                'integer',
                Rule::exists('training_groups', 'id')->where(
                    fn ($query) => $query->where('school_id', $schoolId)
                ),
            ],
        ];
    }
}
