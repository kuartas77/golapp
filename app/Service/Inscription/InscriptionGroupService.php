<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InscriptionGroupService
{
    /** @param array<string, mixed> $requestData */
    public function prepareTrainingGroupData(array &$requestData): void
    {
        $trainingGroup = TrainingGroup::query()
            ->orderBy('id')
            ->where('is_complementary', false)
            ->firstWhere('school_id', $requestData['school_id']);

        throw_if(is_null($trainingGroup), Exception::class, 'Training group not found for school');
        $trainingGroupId = $requestData['training_group_id'] ?? $trainingGroup->id;

        $requestData['training_group_id'] = $trainingGroupId;
        $requestData['pre_inscription'] = (bool) data_get($requestData, 'pre_inscription', false)
            || (string) $trainingGroupId === (string) $trainingGroup->id;
    }

    /** @param array<string, mixed> $requestData */
    public function syncCompetitionGroups(Inscription $inscription, array $requestData): void
    {
        $inscription->competitionGroup()->sync(
            data_get($requestData, 'competition_groups', [])
        );
    }

    /** @param array<int, int|string> $groupIds */
    public function syncComplementaryGroups(Inscription $inscription, array $groupIds): void
    {
        $groupIds = collect($groupIds)
            ->filter(fn ($groupId) => ! blank($groupId))
            ->map(fn ($groupId) => (int) $groupId)
            ->unique()
            ->values();

        $currentGroupIds = $inscription->complementaryGroups()
            ->pluck('training_groups.id')
            ->map(fn ($groupId) => (int) $groupId);

        if ($inscription->complementary_group_id) {
            $currentGroupIds->push((int) $inscription->complementary_group_id);
        }

        $removedGroupIds = $currentGroupIds->unique()->diff($groupIds)->values();
        $year = (int) Carbon::parse($inscription->start_date)->year;

        if ($removedGroupIds->isNotEmpty()) {
            $inscription->assistance()
                ->withTrashed()
                ->where('year', $year)
                ->whereIn('training_group_id', $removedGroupIds)
                ->delete();
        }

        $syncPayload = $groupIds
            ->mapWithKeys(fn (int $groupId) => [$groupId => ['school_id' => $inscription->school_id]])
            ->all();

        $inscription->complementaryGroups()->sync($syncPayload);

        foreach ($groupIds as $groupId) {
            $this->ensureInitialAssistForGroup($inscription, (int) $groupId);
        }
    }

    public function ensureInitialAssists(Inscription $inscription): void
    {
        $this->ensureInitialAssistForGroup($inscription, (int) $inscription->training_group_id);

        foreach ($this->complementaryGroupIdsFor($inscription) as $groupId) {
            $this->ensureInitialAssistForGroup($inscription, $groupId);
        }
    }

    private function complementaryGroupIdsFor(Inscription $inscription): Collection
    {
        return $inscription->complementaryGroups()
            ->pluck('training_groups.id')
            ->push($inscription->complementary_group_id)
            ->filter()
            ->map(fn ($groupId) => (int) $groupId)
            ->unique()
            ->values();
    }

    private function ensureInitialAssistForGroup(Inscription $inscription, int $trainingGroupId): void
    {
        $startDate = Carbon::parse($inscription->start_date);

        $assist = Assist::query()
            ->withTrashed()
            ->firstOrNew([
                'inscription_id' => $inscription->id,
                'training_group_id' => $trainingGroupId,
                'year' => (int) $startDate->year,
                'month' => (int) $startDate->month,
                'school_id' => $inscription->school_id,
            ]);

        $assist->forceFill(['deleted_at' => null])->save();
    }
}
