<?php

declare(strict_types=1);

namespace App\Service\Groups;

use App\Service\Kpi\KpiCacheService;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GroupCatalogCache
{
    public const TRAINING = 'training';

    public const COMPETITION = 'competition';

    public function __construct(private KpiCacheService $kpiCacheService) {}

    public function remember(
        string $type,
        int $schoolId,
        string $variant,
        Closure $resolver,
        ?int $instructorId = null,
        ?int $year = null,
    ): mixed {
        $scope = $instructorId ? "instructor:{$instructorId}" : 'all';
        $year ??= now()->year;
        $version = $this->version($schoolId);
        $key = "group-catalog:v{$version}:school:{$schoolId}:type:{$type}:scope:{$scope}:year:{$year}:variant:{$variant}";

        return Cache::remember($key, now()->addMinutes(5), $resolver);
    }

    public function invalidateSchool(int $schoolId): string
    {
        $version = (string) Str::uuid();
        Cache::forever($this->versionKey($schoolId), $version);
        $this->kpiCacheService->invalidateSchool($schoolId);

        return $version;
    }

    public function version(int $schoolId): string
    {
        return (string) Cache::rememberForever(
            $this->versionKey($schoolId),
            fn () => (string) Str::uuid()
        );
    }

    private function versionKey(int $schoolId): string
    {
        return "group-catalog:version:school:{$schoolId}";
    }
}
