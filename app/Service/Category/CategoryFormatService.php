<?php

declare(strict_types=1);

namespace App\Service\Category;

use App\Models\School;
use App\Models\Setting;
use App\Models\SettingValue;

class CategoryFormatService
{
    /** @var array<int, string> */
    private array $schoolModes = [];

    public const SUB_AGE = 'sub_age';

    public const BIRTH_YEAR = 'birth_year';

    public const BIRTH_YEAR_PREFIX = 'CAT-';

    public const FORMATS = [
        self::SUB_AGE,
        self::BIRTH_YEAR,
    ];

    public function formatBirthYear(int $birthYear, School|int $school, ?int $referenceYear = null): string
    {
        return $this->formatBirthYearForMode(
            $birthYear,
            $this->modeForSchool($school),
            $referenceYear
        );
    }

    public function formatBirthYearForMode(int $birthYear, string $mode, ?int $referenceYear = null): string
    {
        if ($mode === self::BIRTH_YEAR) {
            return self::BIRTH_YEAR_PREFIX.$birthYear;
        }

        return 'SUB-'.abs($birthYear - ($referenceYear ?? now()->year));
    }

    public function modeForSchool(School|int $school): string
    {
        if ($school instanceof School) {
            $settings = $school->getAttribute('settings');
            $mode = $settings?->get(Setting::CATEGORY_FORMAT);
        } else {
            $mode = $this->schoolModes[$school] ??= SettingValue::query()
                ->where('school_id', $school)
                ->where('setting_key', Setting::CATEGORY_FORMAT)
                ->value('value');
        }

        return in_array($mode, self::FORMATS, true) ? $mode : self::SUB_AGE;
    }

    public function forgetSchool(int $schoolId): void
    {
        unset($this->schoolModes[$schoolId]);
    }

    public function convertLabel(string $category, string $targetMode, ?int $referenceYear = null): string
    {
        $birthYear = $this->birthYearFromLabel($category, $referenceYear);

        return $birthYear === null
            ? $category
            : $this->formatBirthYearForMode($birthYear, $targetMode, $referenceYear);
    }

    public function normalizeCategories(array $categories): array
    {
        return collect($categories)
            ->map(fn ($category) => trim((string) $category))
            ->filter(fn (string $category) => $category !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function birthYearFromLabel(string $category, ?int $referenceYear = null): ?int
    {
        $category = trim($category);

        if (preg_match('/^SUB-(\d{1,2})$/', $category, $matches) === 1) {
            return ($referenceYear ?? now()->year) - (int) $matches[1];
        }

        if (preg_match('/^(?:CAT|Categoria)-(\d{4})$/', $category, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
