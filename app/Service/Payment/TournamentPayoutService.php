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

        list($pdf, $excel) = $this->makeLinks($data, $deleted);

        return [
            'group_name' => $groupName,
            'rows' => $rows,
            'count' => $tournamentPayouts->count(),
            'url_print' => $pdf,
            'url_print_excel' => $excel
        ];
    }

    public function generateData($tournamentPayouts, CompetitionGroup $competitionGroup, array $data, bool $deleted = false): array
    {
        $tournamentPayouts = $tournamentPayouts->get();
        $groupName = $competitionGroup->full_name_group;

        list($pdf, $excel) = $this->makeLinks($data, $deleted);

        return [
            'group_name' => $groupName,
            'rows' => $tournamentPayouts,
            'count' => $tournamentPayouts->count(),
            'url_print' => $pdf,
            'url_print_excel' => $excel
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

    private function makeLinks(array $data, bool $deleted): array
    {
        $params = [
            'tournament_id' => $data['tournament_id'],
            'competition_group_id' => $data['competition_group_id'],
            'unique_code' => $data['unique_code'] ?? null,
            'deleted' => $deleted
        ];

        return [
            route('export.tournaments.payouts.pdf', $params),
            route('export.tournaments.payouts.excel', $params),
        ];
    }
}
