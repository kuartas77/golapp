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
            ->select('methodology_records.*')
            ->schoolId()
            ->with(['user:id,name', 'trainingGroup:id,name,category'])
            ->when(isInstructor(), fn (Builder $query) => $query->where('methodology_records.user_id', auth()->id()));
    }

    public function datatableQuery(?string $type = null): Builder
    {
        return $this->query()
            ->leftJoin('users', 'users.id', '=', 'methodology_records.user_id')
            ->leftJoin('training_groups', 'training_groups.id', '=', 'methodology_records.training_group_id')
            ->when($type, fn (Builder $query) => $query->where('methodology_records.type', $type));
    }

    public function list(?string $type = null): Collection
    {
        return $this->query()
            ->when($type, fn (Builder $query) => $query->where('methodology_records.type', $type))
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
