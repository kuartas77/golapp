<?php

namespace App\Observers;

use App\Models\TrainingGroup;

class TrainingGroupObserver
{
    public function deleting(TrainingGroup $trainingGroup)
    {
        $firtsTrainigGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', getSchool(auth()->user())->id)->id ?? null;

        $trainingGroup->load('payments', 'inscriptions');
        $trainingGroup->inscriptions()->update(['training_group_id' => $firtsTrainigGroup]);
        $trainingGroup->payments()->update(['training_group_id' => $firtsTrainigGroup]);
    }
}
