<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Portal;

use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class GuardianPlayerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('guardians')->check();
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png'])
                    ->extensions(['jpg', 'jpeg', 'png'])
                    ->max(3 * 1024),
            ],
            'names' => ['required', 'string', 'max:50'],
            'last_names' => ['required', 'string', 'max:50'],
            'date_birth' => ['required', 'date_format:Y-m-d'],
            'place_birth' => ['required', 'string', 'max:100'],
            'document_type' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'medical_history' => ['nullable', 'string', 'max:200'],
            'school' => ['required', 'string', 'max:50'],
            'degree' => ['required', 'string', 'max:50'],
            'jornada' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:50'],
            'municipality' => ['required', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:50'],
            'rh' => ['required', 'string', 'max:50'],
            'eps' => ['required', 'string', 'max:50'],
            'student_insurance' => ['nullable', 'string', 'max:50'],
            'phones' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $year = Carbon::parse($this->date_birth)->year;
        $player = Player::query()->find($this->route('player'));

        $this->merge([
            'email' => filled($this->email) ? mb_strtolower(trim((string) $this->email)) : null,
            'category' => $player?->school_id
                ? categoriesName($year, (int) $player->school_id)
                : categoriesName($year),
            'school_id' => $player?->school_id,
        ]);
    }
}
