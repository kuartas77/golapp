<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Inscription;
use App\Models\School;
use App\Models\Setting;
use Illuminate\Validation\ValidationException;

class InscriptionLimitService
{
    public const DEFAULT_LIMIT = 200;

    public function summary(School $school, int $year): array
    {
        $limit = $this->limitForSchool($school);
        $current = $this->activeCount($school, $year);

        return [
            'year' => $year,
            'current' => $current,
            'limit' => $limit,
            'remaining' => max(0, $limit - $current),
            'is_full' => $current >= $limit,
        ];
    }

    public function assertCanCreate(School $school, int $year): void
    {
        $summary = $this->summary($school, $year);

        if (! $summary['is_full']) {
            return;
        }

        throw ValidationException::withMessages([
            'max_inscriptions' => sprintf(
                'La escuela alcanzó el límite de %s inscripciones activas para el año %s.',
                $summary['limit'],
                $summary['year']
            ),
        ]);
    }

    public function limitForSchool(School $school): int
    {
        $school->loadMissing('settingsValues');

        $value = filter_var(
            data_get($school, 'settings.' . Setting::MAX_INSCRIPTIONS),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        return $value === false || $value === null
            ? self::DEFAULT_LIMIT
            : (int) $value;
    }

    private function activeCount(School $school, int $year): int
    {
        return Inscription::query()
            ->where('school_id', $school->id)
            ->where('year', $year)
            ->count();
    }
}
