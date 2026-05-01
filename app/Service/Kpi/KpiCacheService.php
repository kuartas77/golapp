<?php

declare(strict_types=1);

namespace App\Service\Kpi;

use Closure;
use Illuminate\Support\Facades\Cache;

class KpiCacheService
{
    public function currentVersion(int $schoolId): int
    {
        return (int) (Cache::get($this->versionKey($schoolId)) ?: 1);
    }

    public function rememberFilters(int $schoolId, Closure $resolver): array
    {
        return Cache::remember(
            $this->filtersKey($schoolId),
            now()->addMinutes(15),
            fn () => $resolver()
        );
    }

    public function rememberPayload(
        int $schoolId,
        string $scopeKey,
        int $year,
        int $month,
        ?int $trainingGroupId,
        Closure $resolver
    ): array {
        return Cache::remember(
            $this->payloadKey($schoolId, $scopeKey, $year, $month, $trainingGroupId),
            now()->addMinutes(5),
            fn () => $resolver()
        );
    }

    public function invalidateSchool(int $schoolId): int
    {
        $nextVersion = $this->currentVersion($schoolId) + 1;

        Cache::forever($this->versionKey($schoolId), $nextVersion);

        return $nextVersion;
    }

    private function versionKey(int $schoolId): string
    {
        return "kpis:version:school:{$schoolId}";
    }

    private function filtersKey(int $schoolId): string
    {
        $version = $this->currentVersion($schoolId);

        return "kpis:filters:v{$version}:school:{$schoolId}";
    }

    private function payloadKey(
        int $schoolId,
        string $scopeKey,
        int $year,
        int $month,
        ?int $trainingGroupId
    ): string {
        $version = $this->currentVersion($schoolId);
        $groupKey = $trainingGroupId ? (string) $trainingGroupId : 'all';

        return "kpis:payload:v{$version}:school:{$schoolId}:scope:{$scopeKey}:year:{$year}:month:{$month}:group:{$groupKey}";
    }
}
