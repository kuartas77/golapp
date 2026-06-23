<?php

namespace App\Observers;

use App\Models\TrainingGroup;
use App\Service\Groups\GroupCatalogCache;

class TrainingGroupObserver
{
    public function __construct(private GroupCatalogCache $groupCatalogCache) {}

    public function deleting(TrainingGroup $trainingGroup)
    {
        $firtsTrainigGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', $trainingGroup->school_id)?->id;

        $trainingGroup->load('payments', 'inscriptions');
        $trainingGroup->inscriptions()->update(['training_group_id' => $firtsTrainigGroup]);
        $trainingGroup->payments()->update(['training_group_id' => $firtsTrainigGroup]);
    }

    public function saved(TrainingGroup $trainingGroup): void
    {
        $this->groupCatalogCache->invalidateSchool((int) $trainingGroup->school_id);
    }

    public function deleted(TrainingGroup $trainingGroup): void
    {
        $this->groupCatalogCache->invalidateSchool((int) $trainingGroup->school_id);
    }

    public function restored(TrainingGroup $trainingGroup): void
    {
        $this->groupCatalogCache->invalidateSchool((int) $trainingGroup->school_id);
    }
}
