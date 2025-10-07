<?php

namespace App\Service\Assist;

use App\Models\TrainingGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class AssistService
{
    public function generateTable($assists, TrainingGroup $trainingGroup, array $params, bool $deleted = false): array
    {
        $group_name = $trainingGroup->full_schedule_group;
        $assists = $assists->get();

        $classDays = classDays(
            $params['year'],
            $params['month'],
            array_map('dayToNumber', $trainingGroup->explode_days)
        );

        $table = $this->makeTable($assists, $classDays, $deleted, $params);

        $links = $this->makeLinks($params, $deleted);

        return [
            'table' => $table,
            'group_name' => $group_name,
            'count' => $assists->count(),
            'url_print' => $links[0],
            'url_print_excel' => $links[1]
        ];
    }

    public function generateData($assists, TrainingGroup $trainingGroup, array $params, bool $deleted = false): array
    {
        $group_name = $trainingGroup->full_schedule_group;
        $assists = $assists->get();

        [$urlExportPDF, $urlExportExcel] = $this->makeLinks($params, $deleted);;

        return $this->generateResponse($assists, $group_name, $urlExportPDF, $urlExportExcel);
    }

    private function generateResponse($rows, $group_name, $urlExportPDF, $urlExportExcel, array $extra = []): array
    {
        $response = [
            'rows' => $rows,
            'group_name' => $group_name,
            'count' => $rows->count(),
            'url_print' => $urlExportPDF,
            'url_print_excel' => $urlExportExcel
        ];

        return array_merge($response, $extra);
    }

    private function makeTable($assists, Collection $classDays, bool $deleted, $params): string
    {
        $rows = '';
        $column = isset($params['column']) ? $params['column'] : null;
        foreach ($assists as $assist) {
            $rows .= View::make('templates.assists.row', [
                'assist' => $assist,
                'classDays' => $classDays,
                'deleted' => $deleted,
                'column' => $column
            ])->render();
        }

        return View::make('templates.assists.table', [
            'thead' => View::make('templates.assists.thead', ['classDays' => $classDays, 'column' => $column])->render(),
            'rows' => $rows
        ])->render();
    }

    private function makeLinks(array $params, bool $deleted): array
    {
        $params = [
            'training_group_id' => $params['training_group_id'],
            'year' => $params['year'],
            'month' => $params['month'],
            'deleted' => $deleted
        ];

        return [
            route('export.pdf.assists', $params),
            route('export.assists', $params)
        ];
    }
}
