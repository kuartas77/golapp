<?php

namespace App\Observers;

use App\Models\TrainingGroup;

class TrainingGroupObserver
{
    public function deleting(TrainingGroup $trainingGroup)
    {
        $trainingGroup->load('payments', 'inscriptions');
        $trainingGroup->inscriptions()->update(['training_group_id', 1]);
        $trainingGroup->payments()->delete();
    }
}
