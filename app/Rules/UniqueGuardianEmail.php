<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\People;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueGuardianEmail implements ValidationRule
{
    public function __construct(
        private readonly ?string $identificationCard = null,
        private readonly ?int $ignoreGuardianId = null
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = filled($value) ? mb_strtolower(trim((string) $value)) : null;

        if (!checkEmail($email)) {
            return;
        }

        $query = People::query()
            ->where('tutor', true)
            ->where('email', $email);

        if (filled($this->identificationCard)) {
            $query->where('identification_card', '!=', $this->identificationCard);
        }

        if ($this->ignoreGuardianId) {
            $query->whereKeyNot($this->ignoreGuardianId);
        }

        if ($query->exists()) {
            $fail('El correo del acudiente ya está registrado en otra cuenta.');
        }
    }
}
