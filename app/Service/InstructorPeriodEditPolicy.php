<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\School;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class InstructorPeriodEditPolicy
{
    public const LOCK_MESSAGE = 'Este periodo ya está cerrado para instructores. Solicita a la escuela una corrección administrativa.';

    public function enabled(?School $school = null): bool
    {
        if (! isInstructor()) {
            return false;
        }

        $school ??= getSchool(auth()->user());

        return filter_var(
            data_get($school, 'settings.' . Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function canMutateDate(mixed $date, ?School $school = null): bool
    {
        if (! $this->enabled($school)) {
            return true;
        }

        $date = $this->parseDate($date);

        if (! $date) {
            return false;
        }

        $now = now();

        return (int) $date->year === (int) $now->year
            && (int) $date->month === (int) $now->month;
    }

    public function canMutateYearMonth(int|string|null $year, int|string|null $month, ?School $school = null): bool
    {
        if (! $this->enabled($school)) {
            return true;
        }

        return (int) $year === (int) now()->year
            && (int) $month === (int) now()->month;
    }

    public function assertCanMutateDate(mixed $date, ?string $field = null, ?School $school = null): void
    {
        if ($this->canMutateDate($date, $school)) {
            return;
        }

        $this->throwLocked($field);
    }

    public function assertCanMutateYearMonth(
        int|string|null $year,
        int|string|null $month,
        ?string $field = null,
        ?School $school = null
    ): void {
        if ($this->canMutateYearMonth($year, $month, $school)) {
            return;
        }

        $this->throwLocked($field);
    }

    private function parseDate(mixed $date): ?CarbonInterface
    {
        if ($date instanceof CarbonInterface) {
            return $date;
        }

        if ($date === null || $date === '') {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function throwLocked(?string $field = null): void
    {
        throw ValidationException::withMessages([
            $field ?: 'period' => [self::LOCK_MESSAGE],
        ]);
    }
}
