<?php

namespace App\Service\Payment;

use App\Models\CompetitionGroup;
use Illuminate\Support\Facades\View;

class TournamentPayoutService
{
    public function generateTable($tournamentPayouts, CompetitionGroup $competitionGroup, array $data, bool $deleted = false): array
    {
        $tournamentPayouts = $tournamentPayouts->get();
        $groupName = $competitionGroup->full_name_group;

        $rows = $this->makeRows($tournamentPayouts, $groupName, $deleted);

        return [
            'group_name' => $groupName,
            'rows' => $rows,
            'count' => $tournamentPayouts->count()
        ];
    }

    private function makeRows($tournamentPayouts, $groupName, bool $deleted): string
    {
        $rows = '';
        foreach ($tournamentPayouts as $tournamentPayout) {
            $rows .= View::make('templates.payments.tournaments.row', [
                'tournamentPayout' => $tournamentPayout,
                'group' => $groupName,
                'deleted' => $deleted
            ])->render();
        }
        return $rows;
    }

    private function makeLinks()
    {

    }
}
