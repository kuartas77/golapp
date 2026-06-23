<?php

namespace App\Observers;

use App\Models\CompetitionGroup;
use App\Service\Groups\GroupCatalogCache;

class CompetitionGroupObserver
{
    public function __construct(private GroupCatalogCache $groupCatalogCache) {}

    public function saved(CompetitionGroup $group): void
    {
        $this->groupCatalogCache->invalidateSchool((int) $group->school_id);
    }

    public function deleted(CompetitionGroup $group): void
    {
        $this->groupCatalogCache->invalidateSchool((int) $group->school_id);
    }

    public function restored(CompetitionGroup $group): void
    {
        $this->groupCatalogCache->invalidateSchool((int) $group->school_id);
    }
}
