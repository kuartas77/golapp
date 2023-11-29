<?php

namespace App\Observers;

use App\Models\School;

class SchoolObserver
{
    /**
     * Handle the skills control "created" event.
     *
     * @param School $school
     * @return void
     */
    public function created(School $school): void
    {
        $school->configDefault();
    }

    /**
     * Handle the skills control "updated" event.
     *
     * @param School $school
     * @return void
     */
    public function updated(School $school)
    {
        //
    }

    /**
     * Handle the skills control "deleted" event.
     *
     * @param School $school
     * @return void
     */
    public function deleted(School $school): void
    {
        $school->users()->delete();
    }

    /**
     * Handle the skills control "restored" event.
     *
     * @param School $school
     * @return void
     */
    public function restored(School $school): void
    {
        $school->users()->restore();
    }

    /**
     * Handle the skills control "force deleted" event.
     *
     * @param School $school
     * @return void
     */
    public function forceDeleted(School $school)
    {
        //
    }
}
