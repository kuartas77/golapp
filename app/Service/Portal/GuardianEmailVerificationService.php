<?php

declare(strict_types=1);

namespace App\Service\Portal;

use App\Models\People;
use App\Models\School;
use App\Notifications\GuardianEmailVerificationCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuardianEmailVerificationService
{
    private const CODE_TTL_SECONDS = 600;
    private const RESEND_SECONDS = 60;
    private const TOKEN_TTL_SECONDS = 1800;
    private const MAX_CODE_ATTEMPTS = 5;

    public function requestCode(School $school, string $document, string $email, string $ip): array
    {
        [$document, $email] = $this->normalize($document, $email);

        if ($this->isPreviouslyVerified($document, $email)) {
            return ['already_verified' => true];
        }

        $context = $this->context($school, $document, $email);
        $resendKey = "guardian-email-verification:resend:{$context}";

        if (RateLimiter::tooManyAttempts($resendKey, 1)) {
            throw ValidationException::withMessages([
                'tutor_email' => ['Espera un minuto antes de solicitar otro código.'],
            ]);
        }

        $this->enforceHourlyLimit("email:{$context}", 5);
        $this->enforceHourlyLimit('ip:'.hash('sha256', $ip), 20);
        $this->enforceHourlyLimit("school:{$school->id}", 100);

        $code = (string) random_int(100000, 999999);
        Cache::put($this->codeKey($context), [
            'hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds(self::CODE_TTL_SECONDS)->timestamp,
        ], self::CODE_TTL_SECONDS);
        RateLimiter::hit($resendKey, self::RESEND_SECONDS);

        Notification::route('mail', $email)
            ->notify(new GuardianEmailVerificationCodeNotification($code, $school->name));

        return ['already_verified' => false, 'expires_in' => self::CODE_TTL_SECONDS];
    }

    public function confirmCode(School $school, string $document, string $email, string $code): array
    {
        [$document, $email] = $this->normalize($document, $email);

        if ($this->isPreviouslyVerified($document, $email)) {
            return ['already_verified' => true, 'token' => null];
        }

        $context = $this->context($school, $document, $email);
        $key = $this->codeKey($context);
        $stored = Cache::get($key);

        if (! is_array($stored)) {
            throw ValidationException::withMessages([
                'verification_code' => ['El código venció o no existe. Solicita uno nuevo.'],
            ]);
        }

        if (($stored['attempts'] ?? 0) >= self::MAX_CODE_ATTEMPTS) {
            Cache::forget($key);
            throw ValidationException::withMessages([
                'verification_code' => ['Superaste el número de intentos. Solicita un código nuevo.'],
            ]);
        }

        if (! Hash::check($code, (string) $stored['hash'])) {
            $stored['attempts'] = ($stored['attempts'] ?? 0) + 1;
            Cache::put($key, $stored, max(1, (int) ($stored['expires_at'] ?? time()) - time()));
            throw ValidationException::withMessages([
                'verification_code' => ['El código ingresado no es correcto.'],
            ]);
        }

        Cache::forget($key);
        $token = Str::random(64);
        Cache::put($this->tokenKey($token), $context, self::TOKEN_TTL_SECONDS);

        return ['already_verified' => false, 'token' => $token];
    }

    public function isVerified(School $school, string $document, string $email, ?string $token): bool
    {
        [$document, $email] = $this->normalize($document, $email);

        if ($this->isPreviouslyVerified($document, $email)) {
            return true;
        }

        return filled($token)
            && hash_equals(
                $this->context($school, $document, $email),
                (string) Cache::get($this->tokenKey((string) $token), '')
            );
    }

    public function consume(?string $token): void
    {
        if (filled($token)) {
            Cache::forget($this->tokenKey((string) $token));
        }
    }

    private function isPreviouslyVerified(string $document, string $email): bool
    {
        return People::query()
            ->where('tutor', true)
            ->where('identification_card', $document)
            ->where('email', $email)
            ->whereNotNull('email_verified_at')
            ->exists();
    }

    private function enforceHourlyLimit(string $suffix, int $maxAttempts): void
    {
        $key = "guardian-email-verification:hourly:{$suffix}";

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'tutor_email' => ['Se alcanzó el límite de códigos. Inténtalo nuevamente más tarde.'],
            ]);
        }

        RateLimiter::hit($key, 3600);
    }

    private function normalize(string $document, string $email): array
    {
        return [trim($document), mb_strtolower(trim($email))];
    }

    private function context(School $school, string $document, string $email): string
    {
        return hash('sha256', "{$school->id}|{$document}|{$email}");
    }

    private function codeKey(string $context): string
    {
        return "guardian-email-verification:code:{$context}";
    }

    private function tokenKey(string $token): string
    {
        return 'guardian-email-verification:token:'.hash('sha256', $token);
    }
}
