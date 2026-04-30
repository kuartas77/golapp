<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupAssignmentCompetitionBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAnyRole(['super-admin', 'school']);
    }

    public function rules(): array
    {
        $schoolId = (int) getSchool($this->user())->id;

        return [
            'competition_group_id' => [
                'nullable',
                'integer',
                Rule::exists('competition_groups', 'id')->where(
                    fn ($query) => $query->where('school_id', $schoolId)
                ),
            ],
        ];
    }
}
