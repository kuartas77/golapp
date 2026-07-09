<?php

namespace App\Observers;

use App\Models\Inscription;
use App\Service\Groups\GroupCatalogCache;
use App\Service\SharedService;

class InscriptionObserver
{
    public function __construct(private SharedService $sharedService, private GroupCatalogCache $groupCatalogCache) {}

    /**
     * Handle the inscription "created" event.
     */
    public function created(Inscription $inscription): void
    {
        $this->sharedService->paymentAssist($inscription);
        $this->invalidateGroupCatalog($inscription);
    }

    /**
     * Handle the inscription "updated" event.
     */
    public function updated(Inscription $inscription): void
    {
        $this->sharedService->paymentAssist($inscription);
        if ($inscription->wasChanged(['training_group_id', 'complementary_group_id', 'year', 'deleted_at'])) {
            $this->invalidateGroupCatalog($inscription);
        }
    }

    /**
     * Handle the inscription "deleted" event.
     */
    public function deleted(Inscription $inscription): void
    {
        $this->invalidateGroupCatalog($inscription);
    }

    /**
     * Handle the inscription "restored" event.
     *
     * @return void
     */
    public function restored(Inscription $inscription)
    {
        $this->invalidateGroupCatalog($inscription);
    }

    /**
     * Handle the inscription "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Inscription $inscription)
    {
        //
    }

    private function invalidateGroupCatalog(Inscription $inscription): void
    {
        if ($inscription->school_id) {
            $this->groupCatalogCache->invalidateSchool((int) $inscription->school_id);
        }
    }
}
