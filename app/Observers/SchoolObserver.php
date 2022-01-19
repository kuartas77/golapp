<?php

namespace App\Observers;

use App\Models\Day;
use App\Models\Schedule;
use App\Models\School;
use App\Models\TrainingGroup;

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
        $day = Day::query()->firstOrCreate([
            'days' => 'Lunes,Miércoles'
        ]);

        $schedule = Schedule::create([
            'schedule' => "10:00AM - 11:00AM",
            'school_id' => $school->id
        ]);
        TrainingGroup::create([
            'name' => 'Provicional',
            'year' => now()->year,
            'category' => 'Todas Las Categorías',
            'day_id' => $day->id,
            'schedule_id' => $schedule->id,
            'school_id' => $school->id
        ]);
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
