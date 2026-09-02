<?php

namespace App\Observers;

use App\Models\School;
use App\Models\SchoolInvoiceSequence;
use App\Service\Auth\AuthUserContext;
use App\Service\Kpi\KpiCacheService;

class SchoolObserver
{
    /**
     * Handle the skills control "created" event.
     */
    public function created(School $school): void
    {
        $school->configDefault();
        SchoolInvoiceSequence::query()->firstOrCreate(
            ['school_id' => $school->id],
            ['next_number' => 1]
        );
    }

    /**
     * Handle the skills control "updated" event.
     */
    public function updated(School $school): void
    {
        School::forgetCachedSchool($school->id);
        AuthUserContext::forgetSchool($school->id);
        app(KpiCacheService::class)->invalidateSchool((int) $school->id);
    }

    /**
     * Handle the skills control "deleted" event.
     */
    public function deleted(School $school): void
    {
        $school->users()->delete();
    }

    /**
     * Handle the skills control "restored" event.
     */
    public function restored(School $school): void
    {
        $school->users()->restore();
    }

    /**
     * Handle the skills control "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(School $school)
    {
        //
    }
}
