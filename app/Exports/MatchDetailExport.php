<?php

namespace App\Exports;

use App\Repositories\GameRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class MatchDetailExport implements ShouldQueue, FromView, WithTitle, WithStyles , ShouldAutoSize, WithEvents
{
    use Exportable;

    private $competition_group;

    public function __construct($competition_group)
    {
        $this->competition_group = $competition_group;
    }

    public function view(): View
    {
        $inscriptions = app(GameRepository::class)->exportMatchDetail($this->competition_group);

        return view('exports.match_excel', [
            'inscriptions' => $inscriptions,
            'cantidad' => count($inscriptions)
        ]);
    }

    public function title(): string
    {
        return "Detalle del partido";
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
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '#000000'],
                        ],
                    ],
                ]);
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $scores = Cache::rememberForever('KEY_SCORE', function () {
            $scores = collect();
            for ($i = 0; $i <= 10; ++$i) {
                $scores->put($i, $i);
            }

            return $scores;
        });

        $qualifications = Cache::rememberForever('KEY_SCORE_QUA', function () {
            $qualifications = collect();
            for ($i = 1; $i <= 5; ++$i) {
                $qualifications->put($i, $i);
            }

            return $qualifications;
        });

        $this->addDropdown($sheet, "C", [1=>'Sí',0 => 'No']);
        $this->addDropdown($sheet, "D", [1=>'Sí',0 => 'No']);
        $this->addDropdownMin($sheet, "E");
        $this->addDropdown($sheet, "F", config('variables.KEY_POSITIONS', []));
        $this->addDropdown($sheet, "G", $scores->toArray());
        $this->addDropdown($sheet, "H", $scores->toArray());
        $this->addDropdown($sheet, "I", $scores->toArray());
        $this->addDropdown($sheet, "J", ['0'=>'0', '1'=>'1','2'=>'2']);
        $this->addDropdown($sheet, "K", ['0'=>'0','1'=>'1']);
        $this->addDropdown($sheet, "L", $qualifications->toArray());

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
    }

    private function addDropdown(Worksheet $sheet, string $cell, array $options)
    {
        for ($i=2; $i < 30; $i++) {
            $objValidation = $sheet->getCell($cell.$i)->getDataValidation();
            $objValidation->setType(DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(false);
            $objValidation->setShowInputMessage(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setPromptTitle('Seleccione una opción.');
            $objValidation->setPrompt('Por favor seleccione un elemento de la lista.');
            $objValidation->setErrorTitle('Error');
            $objValidation->setError('El valor no está en la lista.');
            $objValidation->setFormula1('"'.implode(',', $options).'"');
        }
    }

    private function addDropdownMin(Worksheet $sheet, string $cell)
    {
        for ($i=2; $i < 30; $i++) {
            $objValidation = $sheet->getCell($cell.$i)->getDataValidation();
            $objValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(false);
            $objValidation->setShowInputMessage(true);
            $objValidation->setPromptTitle('Ingrese un número.');
            $objValidation->setPrompt('Por favor ingrese números, el tiempo jugado en minutos.');
            $objValidation->setErrorTitle('Error');
        }
    }

}
