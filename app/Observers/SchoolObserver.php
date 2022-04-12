<?php

namespace App\Observers;

use App\Models\School;
class SchoolObserver
{
    /**
     * Handle the skills control "created" event.
     *
     * @param  \App\Models\School  $school
     * @return void
     */
    public function created(School $school)
    {
        $school->configDefault();
    }

    /**
     * Handle the skills control "updated" event.
     *
     * @param  \App\Models\School  $school
     * @return void
     */
    public function updated(School $school)
    {
        //
    }

    /**
     * Handle the skills control "deleted" event.
     *
     * @param  \App\Models\School  $school
     * @return void
     */
    public function deleted(School $school)
    {
        $school->users()->delete();
    }

    /**
     * Handle the skills control "restored" event.
     *
     * @param  \App\Models\School  $school
     * @return void
     */
    public function restored(School $school)
    {
        $school->users()->restore();
    }

    /**
     * Handle the skills control "force deleted" event.
     *
     * @param  \App\Models\School  $school
     * @return void
     */
    public function forceDeleted(School $school)
    {
        //
    }
}
