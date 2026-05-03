<?php

declare(strict_types=1);

namespace App\Service\Portal;

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

    private function applyEligibleSchoolScope(Builder $query): void
    {
        $query
            ->where('is_enable', true)
            ->where('tutor_platform', true);
    }
}
