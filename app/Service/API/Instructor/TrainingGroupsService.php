<?php

namespace App\Service\API\Instructor;

use App\Models\Inscription;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\TrainingGroup;
use Illuminate\Database\Eloquent\Builder;

class TrainingGroupsService
{
    private function query(): Builder
    {
        return TrainingGroup::query()
            ->when(isInstructor(), fn($q) => $q->byInstructor())
            ->withCount(['inscriptions' => fn($q) => $q->year()])
            ->where('year_active', now()->year)
            ->withWhereHas(
                'members',
                fn($q) => $q->addSelect([
                    'inscription_id' => Inscription::select('id')->whereColumn('players.id', 'inscriptions.player_id')->year()->limit(1)
                ])
            )
            ->schoolId();
    }

    public function getGroups()
    {
        $search = request()->input('q', null);
        $per_page = request()->input('limit', 15);
        $skip = $per_page * (request()->input('page', 1) - 1);

        return $this->query()
            ->when($search != null, fn($query) => $query->where('name', 'like', "%{$search}%"))
            ->skip($skip)
            ->take($per_page)
            ->get();
    }

    public function getGroup(int $id)
    {
        return $this->query()
            ->findOrFail($id);
    }
}
