<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolOutings;

use App\Models\SchoolOuting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolOutingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(SchoolOuting::STATUSES)],
        ];
    }
}
