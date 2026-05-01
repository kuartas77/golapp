<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Assist;
use App\Service\Kpi\KpiCacheService;

class AssistObserver
{
    public function saved(Assist $assist): void
    {
        $this->invalidate($assist);
    }

    public function deleted(Assist $assist): void
    {
        $this->invalidate($assist);
    }

    public function restored(Assist $assist): void
    {
        $this->invalidate($assist);
    }

    public function forceDeleted(Assist $assist): void
    {
        $this->invalidate($assist);
    }

    private function invalidate(Assist $assist): void
    {
        $schoolId = (int) $assist->school_id;

        if ($schoolId <= 0) {
            return;
        }

        app(KpiCacheService::class)->invalidateSchool($schoolId);
    }
}
