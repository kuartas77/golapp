<?php

namespace App\Observers;

use App\Models\Inscription;
use App\Service\SharedService;
use App\Traits\ErrorTrait;

class InscriptionObserver
{
    use ErrorTrait;

    public function __construct(private SharedService $sharedService)
    {

    }

    /**
     * Handle the inscription "created" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function created(Inscription $inscription): void
    {
        $this->sharedService->paymentAssist($inscription);
    }

    /**
     * Handle the inscription "updated" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function updated(Inscription $inscription): void
    {
        $this->sharedService->paymentAssist($inscription);
    }

    /**
     * Handle the inscription "deleted" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function deleted(Inscription $inscription): void
    {
        $inscription->payments()->delete();
        $inscription->assistance()->delete();
    }

    /**
     * Handle the inscription "restored" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function restored(Inscription $inscription)
    {

    }

    /**
     * Handle the inscription "force deleted" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function forceDeleted(Inscription $inscription)
    {
        //
    }
}
