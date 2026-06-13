<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\MethodologyRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MethodologyRecordRepository
{
    public function query(): Builder
    {
        return MethodologyRecord::query()
            ->schoolId()
            ->with(['user:id,name', 'trainingGroup:id,name,category'])
            ->when(isInstructor(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public function list(?string $type = null): Collection
    {
        return $this->query()
            ->when($type, fn (Builder $query) => $query->where('type', $type))
            ->latest()
            ->get();
    }

    public function findAccessibleOrFail(int $id): MethodologyRecord
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $payload): MethodologyRecord
    {
        return MethodologyRecord::query()->create($payload + [
            'school_id' => getSchool(auth()->user())->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function update(MethodologyRecord $record, array $payload): MethodologyRecord
    {
        unset($payload['school_id'], $payload['user_id']);

        $record->fill($payload);
        $record->save();

        return $record->fresh(['user:id,name', 'trainingGroup:id,name,category']);
    }
}
