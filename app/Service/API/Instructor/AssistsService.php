<?php

namespace App\Service\API\Instructor;

use App\Models\Assist;

class AssistsService
{
    public function getAssists(array $params)
    {
        $training_group_id = $params['group_id'];
        $month = $params['month'];
        $year = $params['year'];

        return Assist::query()->schoolId()->with(['player'])
            ->where('training_group_id', $training_group_id)
            ->where('year', $year)
            ->where('month', $month)
            ->get();
    }
}
