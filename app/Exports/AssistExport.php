<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Service\Assist\AssistExportService;

class AssistExport implements FromView, WithTitle
{
    use Exportable;

    private $params;
    private $deleted;
    private $group_name;

    /**
     * PaymentsExport constructor.
     * @param array $params
     * @param $deleted
     */
    public function __construct(array $params, $deleted)
    {
        $this->params = $params;
        $this->deleted = $deleted;
    }

    public function view(): View
    {
        list($assists, $classDays, $group_name, $group) = app(AssistExportService::class)->dataExport($this->params, $this->deleted);

        $this->group_name = $group_name;
        $group->instructors_names = $group->instructors_names;
        return view('exports.assists_excel', [
            'group' => $group,
            'assists' => $assists,
            'classDays' => $classDays,
            'count' => $assists->count() + 1,
            'result' => (40 - $assists->count() + 1),
            'optionAssist' => config('variables.KEY_ASSIST_LETTER')
        ]);
    }

    public function title(): string
    {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        return "Asistencias {$this->group_name}";
    }
}
