<?php

namespace App\Service\Assist;

use App\Models\TrainingGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class AssistService
{
    public function generateTable($assists, TrainingGroup $trainingGroup, array $data, bool $deleted = false): array
    {
        $months = config('variables.KEY_MONTHS_INDEX');
        $group_name = $trainingGroup->full_schedule_group;
        $assists = $assists->get();

        $classDays = classDays(
            $data['year'],
            array_search($data['month'], $months, true),
            array_map('dayToNumber', $trainingGroup->explode_name['days'])
        );

        $table = $this->makeTable($assists, $classDays, $deleted);

        $links = $this->makeLinks($data, $deleted);

        return [
            'table' => $table,
            'group_name' => $group_name,
            'count' => $assists->count(),
            'url_print' => $links[0],
            'url_print_excel' => $links[1]
        ];
    }

    private function makeTable($assists, Collection $classDays, bool $deleted): string
    {
        $rows = '';
        foreach ($assists as $assist) {
            $rows .= View::make('templates.assists.row', [
                'assist' => $assist,
                'classDays' => $classDays,
                'deleted' => $deleted
            ])->render();
        }

        return View::make('templates.assists.table', [
            'thead' => View::make('templates.assists.thead', ['classDays' => $classDays])->render(),
            'rows' => $rows
        ])->render();
    }

    private function makeLinks(array $data, bool $deleted): array
    {
        $params = [
            'training_group_id' => $data['training_group_id'],
            'year' => $data['year'],
            'month' => $data['month'],
            'deleted' => $deleted
        ];

        return [
            route('export.pdf.assists', $params),
            route('export.assists', $params)
        ];
    }
}
