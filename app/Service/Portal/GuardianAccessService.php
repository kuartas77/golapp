<?php

declare(strict_types=1);

namespace App\Service\Portal;

use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GuardianAccessService
{
    public function eligiblePlayersQuery(People $guardian): BelongsToMany
    {
        return $guardian->players()
            ->select('players.*')
            ->whereHas('inscriptions', function (Builder $query) {
                $query->where('year', now()->year)
                    ->whereHas('school', fn (Builder $schoolQuery) => $this->applyEligibleSchoolScope($schoolQuery));
            })
            ->distinct();
    }

    public function eligibleInscriptionsQuery(People $guardian): Builder
    {
        return Inscription::query()
            ->where('year', now()->year)
            ->whereHas('school', fn (Builder $query) => $this->applyEligibleSchoolScope($query))
            ->whereHas('player.people', fn (Builder $query) => $query->where('peoples.id', $guardian->id));
    }

    public function eligibleEvaluationsQuery(People $guardian): Builder
    {
        return PlayerEvaluation::query()
            ->whereHas('inscription', function (Builder $query) use ($guardian) {
                $query->where('year', now()->year)
                    ->whereHas('school', fn (Builder $schoolQuery) => $this->applyEligibleSchoolScope($schoolQuery))
                    ->whereHas('player.people', fn (Builder $peopleQuery) => $peopleQuery->where('peoples.id', $guardian->id));
            });
    }

    private function applyEligibleSchoolScope(Builder $query): void
    {
        $query
            ->where('is_enable', true)
            ->where('tutor_platform', true);
    }

    public function hasEligiblePlayers(People $guardian): bool
    {
        return $this->eligiblePlayersQuery($guardian)->exists();
    }

    public function findEligiblePlayer(People $guardian, int $playerId): Player
    {
        $player = $this->eligiblePlayersQuery($guardian)->firstWhere('players.id', $playerId);

        if (!$player instanceof Player) {
            throw (new ModelNotFoundException())->setModel(Player::class, [$playerId]);
        }

        return $player;
    }

    public function findEligibleInscription(People $guardian, int $inscriptionId): Inscription
    {
        $inscription = $this->eligibleInscriptionsQuery($guardian)->find($inscriptionId);

        if (!$inscription instanceof Inscription) {
            throw (new ModelNotFoundException())->setModel(Inscription::class, [$inscriptionId]);
        }

        return $inscription;
    }

    public function findEligibleEvaluation(People $guardian, int $evaluationId): PlayerEvaluation
    {
        $evaluation = $this->eligibleEvaluationsQuery($guardian)->find($evaluationId);

        if (!$evaluation instanceof PlayerEvaluation) {
            throw (new ModelNotFoundException())->setModel(PlayerEvaluation::class, [$evaluationId]);
        }

        return $evaluation;
    }
}
