<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Base64Image implements ValidationRule
{
    public function __construct(private readonly int $maxBytes = 1048576) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/\Adata:image\/(png|jpeg);base64,([A-Za-z0-9+\/]+={0,2})\z/', $value, $matches) !== 1) {
            $fail('La firma debe ser una imagen PNG o JPEG válida.');

            return;
        }

        $decoded = base64_decode($matches[2], true);

        if ($decoded === false || strlen($decoded) > $this->maxBytes) {
            $fail('La firma no puede superar 1 MB.');

            return;
        }

        $imageInfo = @getimagesizefromstring($decoded);

        if (! is_array($imageInfo) || ! in_array($imageInfo['mime'] ?? null, ['image/png', 'image/jpeg'], true)) {
            $fail('La firma debe contener una imagen PNG o JPEG válida.');
        }
    }
}
