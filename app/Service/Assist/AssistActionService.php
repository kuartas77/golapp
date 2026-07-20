<?php

namespace App\Service\Assist;

use App\Models\Assist;
use App\Repositories\AssistRepository;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Http\Request;

class AssistActionService
{
    public function __construct(
        private AssistRepository $repository,
        private InstructorPeriodEditPolicy $periodEditPolicy
    ) {
    }

    public function search(Request $request): array
    {
        return $this->repository->search(
            params: $request->only(['training_group_id', 'year', 'month', 'column']),
            raw: $request->filled('dataRaw')
        );
    }

    public function create(array $params): array
    {
        return $this->repository->create($params);
    }

    public function bulkUpdate(array $validated): array
    {
        $this->assertInstructorAccess((int) $validated['training_group_id'], (int) $validated['year']);
        $this->periodEditPolicy->assertCanMutateYearMonth($validated['year'], $validated['month'], 'assist');

        if ((int) $validated['year'] !== (int) now()->year) {
            return $this->readonlyYearError();
        }

        return [
            'payload' => ['data' => $this->repository->bulkUpdate($validated)],
            'status' => 200,
        ];
    }

    public function showPayload(Assist $assist, ?string $action, ?string $column, ?string $date): array
    {
        $this->assertInstructorAccess((int) $assist->training_group_id, (int) $assist->year);

        $assist->load(['player']);

        if ($action === 'observation') {
            return [
                'id' => $assist->id,
                'player_name' => $assist->player->full_names,
                'observations' => $this->observationsText($assist),
            ];
        }

        return [
            'id' => $assist->id,
            'player_name' => $assist->player->full_names,
            'value' => data_get($assist, $column),
            'observation' => data_get($assist, "observations.{$date}", ''),
        ];
    }

    public function update(Assist $assist, array $validated): array
    {
        $this->assertInstructorAccess((int) $assist->training_group_id, (int) $assist->year);

        if ($this->repository->assistBelongsToDeletedInscription($assist)) {
            return [
                'payload' => [
                    'message' => AssistRepository::RETIRED_INSCRIPTION_MESSAGE,
                    'errors' => [
                        'assist' => [AssistRepository::RETIRED_INSCRIPTION_MESSAGE],
                    ],
                ],
                'status' => 422,
            ];
        }

        $this->periodEditPolicy->assertCanMutateYearMonth(
            (int) $assist->year,
            (int) $assist->getRawOriginal('month'),
            'assist'
        );

        if ((int) $assist->year !== (int) now()->year) {
            return $this->readonlyYearError();
        }

        return [
            'payload' => $this->repository->update($assist, $validated),
            'status' => 200,
        ];
    }

    private function assertInstructorAccess(int $trainingGroupId, int $year): void
    {
        abort_if(
            isInstructor() && ! instructorCanAccessTrainingGroup($trainingGroupId, $year),
            404
        );
    }

    private function readonlyYearError(): array
    {
        $message = 'Las asistencias de años anteriores son de sólo lectura.';

        return [
            'payload' => [
                'message' => $message,
                'errors' => [
                    'assist' => [$message],
                ],
            ],
            'status' => 422,
        ];
    }

    private function observationsText(Assist $assist): string
    {
        if (! is_object($assist->observations)) {
            return '';
        }

        $observations = '';
        foreach ($assist->observations as $date => $observation) {
            $observations .= $date . ': ' . $observation . PHP_EOL;
        }

        return $observations;
    }
}
