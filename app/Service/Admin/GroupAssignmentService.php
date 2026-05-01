<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Http\Resources\API\Groups\GroupAssignmentItemResource;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Support\Collection;

class GroupAssignmentService
{
    public function __construct(
        private readonly TrainingGroupRepository $trainingGroupRepository,
        private readonly CompetitionGroupRepository $competitionGroupRepository,
    ) {
    }

    public function getTrainingBoard(?int $originGroupId, ?int $targetGroupId): array
    {
        $groups = $this->trainingGroupRepository
            ->getListGroupsSchedule()
            ->unique('id')
            ->values();

        return [
            'selectors' => [
                'origin_groups' => $this->mapTrainingGroupOptions($groups),
                'destination_groups' => $this->mapTrainingGroupOptions($groups),
            ],
            'panels' => [
                'source' => $this->makeTrainingPanel($groups, $originGroupId),
                'destination' => $this->makeTrainingPanel($groups, $targetGroupId),
            ],
        ];
    }

    public function moveTraining(int $inscriptionId, int $targetGroupId): bool
    {
        TrainingGroup::query()->schoolId()->findOrFail($targetGroupId);

        $inscription = Inscription::query()
            ->schoolId()
            ->findOrFail($inscriptionId);

        return (bool) $inscription->update([
            'training_group_id' => $targetGroupId,
        ]);
    }

    public function getCompetitionBoard(?int $competitionGroupId): array
    {
        $selectedGroup = $competitionGroupId
            ? CompetitionGroup::query()->schoolId()->findOrFail($competitionGroupId)
            : null;

        $groups = $this->competitionGroupRepository->getGroupsYear();
        $sourceItems = $this->competitionSourceItems($selectedGroup);

        return [
            'selectors' => [
                'groups' => $groups
                    ->map(fn (string $label, int|string $id) => [
                        'value' => (string) $id,
                        'label' => $label,
                    ])
                    ->values()
                    ->all(),
            ],
            'panels' => [
                'source' => [
                    'group_id' => null,
                    'group_label' => null,
                    'count' => $sourceItems->count(),
                    'items' => $this->serializeItems($sourceItems),
                ],
                'destination' => $this->makeCompetitionDestinationPanel($selectedGroup),
            ],
        ];
    }

    public function moveCompetition(int $inscriptionId, int $competitionGroupId, bool $assign): int
    {
        CompetitionGroup::query()->schoolId()->findOrFail($competitionGroupId);
        Inscription::query()->schoolId()->findOrFail($inscriptionId);

        return (int) $this->competitionGroupRepository->assignInscriptionGroup(
            (string) $inscriptionId,
            (string) $competitionGroupId,
            $assign
        );
    }

    private function mapTrainingGroupOptions(Collection $groups): array
    {
        return $groups
            ->map(fn (TrainingGroup $group) => [
                'value' => (string) $group->id,
                'label' => $group->full_schedule_group,
            ])
            ->values()
            ->all();
    }

    private function makeTrainingPanel(Collection $groups, ?int $groupId): array
    {
        if (!$groupId) {
            return [
                'group_id' => null,
                'group_label' => null,
                'count' => 0,
                'items' => [],
            ];
        }

        $group = $groups->firstWhere('id', $groupId)
            ?? TrainingGroup::query()->schoolId()->findOrFail($groupId);

        $items = $this->trainingItemsForGroup((int) $group->id);

        return [
            'group_id' => $group->id,
            'group_label' => $group->full_schedule_group,
            'count' => $items->count(),
            'items' => $this->serializeItems($items),
        ];
    }

    private function makeCompetitionDestinationPanel(?CompetitionGroup $group): array
    {
        if (!$group) {
            return [
                'group_id' => null,
                'group_label' => null,
                'count' => 0,
                'items' => [],
            ];
        }

        $group->load([
            'inscriptions' => fn ($query) => $query
                ->with('player')
                ->where('year', now()->year),
        ]);

        $items = $this->sortItems($group->inscriptions);

        return [
            'group_id' => $group->id,
            'group_label' => $group->full_name_group,
            'count' => $items->count(),
            'items' => $this->serializeItems($items),
        ];
    }

    private function trainingItemsForGroup(int $groupId): Collection
    {
        $items = Inscription::query()
            ->schoolId()
            ->with('player')
            ->where('year', now()->year)
            ->where('training_group_id', $groupId)
            ->get();

        return $this->sortItems($items);
    }

    private function competitionSourceItems(?CompetitionGroup $selectedGroup): Collection
    {
        $items = Inscription::query()
            ->schoolId()
            ->with('player')
            ->where('year', now()->year)
            ->when($selectedGroup, fn ($query) => $query->whereDoesntHave(
                'competitionGroup',
                fn ($competitionQuery) => $competitionQuery->where('competition_groups.id', $selectedGroup->id)
            ))
            ->get();

        return $this->sortItems($items);
    }

    private function serializeItems(Collection $items): array
    {
        return GroupAssignmentItemResource::collection($items)->resolve();
    }

    private function sortItems(Collection $items): Collection
    {
        return $items
            ->sortBy(fn (Inscription $inscription) => sprintf(
                '%s|%s',
                mb_strtolower($inscription->player?->full_names ?? ''),
                mb_strtolower((string) $inscription->category)
            ))
            ->values();
    }
}
