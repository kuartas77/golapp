<?php

declare(strict_types=1);

namespace App\Observers;

use App\Service\Kpi\KpiCacheService;
use Illuminate\Database\Eloquent\Model;

final class FinancialKpiObserver
{
    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    public function restored(Model $model): void
    {
        $this->invalidate($model);
    }

    public function forceDeleted(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        $schoolId = (int) $model->getAttribute('school_id');

        if ($schoolId > 0) {
            app(KpiCacheService::class)->invalidateSchool($schoolId);
        }
    }
}
