<?php

namespace App\Observers;

use App\Models\SkillsControl;

class SkillsControlObserver
{
    /**
     * Handle the skills control "created" event.
     *
     * @param SkillsControl $skillsControl
     * @return void
     */
    public function created(SkillsControl $skillsControl)
    {
        //
    }

    /**
     * Handle the skills control "updated" event.
     *
     * @param SkillsControl $skillsControl
     * @return void
     */
    public function updated(SkillsControl $skillsControl)
    {
        //
    }

    /**
     * Handle the skills control "deleted" event.
     *
     * @param SkillsControl $skillsControl
     * @return void
     */
    public function deleted(SkillsControl $skillsControl)
    {
        //
    }

    /**
     * Handle the skills control "restored" event.
     *
     * @param SkillsControl $skillsControl
     * @return void
     */
    public function restored(SkillsControl $skillsControl)
    {
        //
    }

    /**
     * Handle the skills control "force deleted" event.
     *
     * @param SkillsControl $skillsControl
     * @return void
     */
    public function forceDeleted(SkillsControl $skillsControl)
    {
        //
    }
}
