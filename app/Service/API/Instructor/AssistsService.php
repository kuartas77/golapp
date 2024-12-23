<?php

namespace App\Service\API\Instructor;

use App\Models\Assist;

class AssistsService
{
    public function getAssists(array $params)
    {
        return Assist::query()->schoolId()->with(['player'])
            ->where('training_group_id', $params['training_group_id'])
            ->where('month', $params['month'])
            ->where('year', $params['year'])
            ->get();
    }
}
