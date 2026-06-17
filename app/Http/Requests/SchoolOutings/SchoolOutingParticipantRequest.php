<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolOutings;

use Illuminate\Foundation\Http\FormRequest;

class SchoolOutingParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    public function rules(): array
    {
        return [
            'inscription_ids' => ['required', 'array', 'min:1'],
            'inscription_ids.*' => ['integer', 'distinct', 'exists:inscriptions,id'],
        ];
    }
}
