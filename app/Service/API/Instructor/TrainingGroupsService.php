<?php

namespace App\Service\API\Instructor;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\TrainingGroup;

class TrainingGroupsService
{

    public function getGroups()
    {
        $search = request()->input('q', null);
        $per_page = request()->input('limit', 15);
        $skip = $per_page * (request()->input('page', 1) - 1);

        return TrainingGroup::query()
            ->when(isInstructor(), fn($q) => $q->byInstructor())
            ->when($search != null, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->schoolId()
            ->skip($skip)
            ->take($per_page)
            ->get();
    }

    public function getGroup(int $id)
    {
        return TrainingGroup::query()->schoolId()
            ->when(isInstructor(), fn($q) => $q->byInstructor())
            ->findOrFail($id);
    }
}
