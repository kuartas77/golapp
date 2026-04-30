<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupAssignmentTrainingBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['super-admin', 'school']);
    }

    public function rules(): array
    {
        $schoolId = (int) getSchool($this->user())->id;

        $groupRule = Rule::exists('training_groups', 'id')->where(
            fn ($query) => $query->where('school_id', $schoolId)
        );

        return [
            'origin_group_id' => ['nullable', 'integer', $groupRule],
            'target_group_id' => ['nullable', 'integer', $groupRule],
        ];
    }
}
