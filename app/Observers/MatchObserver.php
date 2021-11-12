<?php

namespace App\Observers;

use App\Models\Game;

class MatchObserver
{

    /**
     * Handle the match "deleting" event.
     *
     * @param Game $match
     * @return void
     */
    public function deleting(Game $match)
    {
        $match->skillsControls()->forceDelete();
    }
}
