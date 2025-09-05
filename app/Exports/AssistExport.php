<?php

namespace App\Exports;

use App\Service\Assist\AssistExportService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AssistExport implements ShouldQueue, FromView, WithTitle, ShouldAutoSize, WithEvents
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
            'optionAssist' => config('variables.KEY_ASSIST_EXCEL'),
            'month' => config('variables.KEY_MONTHS_INDEX')[$this->params['month']],
        ]);
    }

    public function title(): string
    {
        return "Asistencias {$this->group_name}";
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = $event->sheet->getHighestColumn();
                $lastRow = $event->sheet->getHighestRow();

                $range = 'A1:' . $lastColumn . $lastRow;

                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '#000000'],
                        ],
                    ],
                ]);

                $event->sheet->getDelegate()->getStyle('A1:D2')->getAlignment()->applyFromArray([
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]);
            }
        ];
    }
}
