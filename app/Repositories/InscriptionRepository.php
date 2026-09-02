<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Inscription;
use App\Models\School;
use App\Service\Inscription\InscriptionMutationService;
use App\Service\Inscription\InscriptionYearRenewalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InscriptionRepository
{
    public function __construct(
        private Inscription $inscription,
        private InscriptionMutationService $mutationService,
        private InscriptionYearRenewalService $yearRenewalService
    ) {}

    public function findById(int|string $id, bool $trashed = false): Inscription
    {
        if ($trashed) {
            return Inscription::onlyTrashed()->schoolId()->findOrFail($id);
        }

        return Inscription::query()->schoolId()->findOrFail($id);

    }

    /**
     * @param  array<string, mixed>  $requestData
     * @return array{success: bool, reactivated: bool}
     */
    public function createInscription(array $requestData, ?School $school = null): array
    {
        return $this->mutationService->create($requestData, $school);
    }

    /**
     * @param  array<string, mixed>  $requestData
     */
    public function updateInscription(array $requestData, Inscription $inscription): bool
    {
        return $this->mutationService->update($requestData, $inscription);
    }

    public function getInscriptionsEnabled(): Builder
    {
        return Inscription::query()->select('inscriptions.*')->with(['player.people', 'trainingGroup' => fn ($q) => $q->withTrashed()])
            ->join('players', 'inscriptions.player_id', '=', 'players.id')
            ->inscriptionYear(request('inscription_year'))
            ->schoolId();
    }

    public function getInscriptionsDisabled(): Builder
    {
        return $this->inscription->with(['player.people', 'trainingGroup'])
            ->inscriptionYear(request('inscription_year'))->schoolId()->onlyTrashed();
    }

    /** @param array<string, mixed> $fields */
    public function searchInscriptionCompetition(array $fields): ?Inscription
    {
        return Inscription::query()->with('player')
            ->where('unique_code', $fields['unique_code'])
            ->whereHas(
                'competitionGroup',
                fn ($q) => $q->where('competition_group_id', $fields['competition_group_id']), '<=', 0)
            ->where('year', now()->year)
            ->schoolId()
            ->first();
    }

    public function searchInsUniqueCode(int|string $id): ?Inscription
    {
        $query = $this->inscription->query()
            ->with(['player', 'competitionGroup', 'complementaryGroups'])
            ->schoolId();

        $inscription = null;

        if (is_numeric($id)) {
            $inscription = (clone $query)->find((int) $id);
        }

        if (! $inscription) {
            $inscription = $query
                ->orderByDesc('id')
                ->firstWhere('unique_code', (string) $id);
        }

        if (! $inscription) {
            return null;
        }

        $inscription->setAttribute(
            'competition_groups',
            $inscription->competitionGroup->pluck('id')->map(fn ($groupId) => (string) $groupId)->values()->all()
        );
        $inscription->setAttribute(
            'complementary_group_ids',
            $inscription->complementaryGroups
                ->pluck('id')
                ->push($inscription->complementary_group_id)
                ->filter()
                ->map(fn ($groupId) => (string) $groupId)
                ->unique()
                ->values()
                ->all()
        );

        return $inscription;
    }

    public function disable(Inscription $inscription): bool
    {
        return $this->mutationService->disable($inscription);
    }

    public function createInscriptionByYear(
        int|string|null $actualYear = null,
        int|string|Carbon|null $futureYear = null
    ): void {
        $this->yearRenewalService->createByYear($actualYear, $futureYear);
    }

    public function getPreinscriptionsOrProvicionalGroup(int $schoolId, int $trainingGroupId): Builder
    {
        return Inscription::query()
            ->select([
                'inscriptions.id',
                'inscriptions.unique_code',
                DB::raw("CONCAT(players.names, ' ', players.last_names) as names"),
            ])
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->where('inscriptions.year', now()->year)
            ->where('inscriptions.school_id', $schoolId)
            ->where(
                fn ($query) => $query->where('inscriptions.training_group_id', $trainingGroupId)
                    ->orWhere('inscriptions.pre_inscription', 1));
    }
}
