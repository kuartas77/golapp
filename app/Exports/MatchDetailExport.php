<?php

namespace App\Exports;

use App\Repositories\GameRepository;
use Illuminate\Contracts\View\View;
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

class MatchDetailExport implements FromView, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    use Exportable;

    private $competition_group;

    private $match;

    public function __construct($competition_group = null, $match = null)
    {
        $this->competition_group = $competition_group;
        $this->match = $match;
    }

    public function view(): View
    {
        $inscriptions = $this->match
            ? app(GameRepository::class)->exportMatchDetailFromMatch($this->match)
            : app(GameRepository::class)->exportMatchDetail($this->competition_group);

        return view('exports.match_excel', [
            'inscriptions' => $inscriptions,
            'cantidad' => count($inscriptions),
        ]);
    }

    public function title(): string
    {
        return 'Detalle del partido';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                $validationLastRow = max($lastRow, 30);

                $this->addInlineDropdown($sheet, 'C', ['Sí', 'No'], $validationLastRow);
                $this->addInlineDropdown($sheet, 'D', ['Sí', 'No'], $validationLastRow);
                $this->addDropdownMin($sheet, 'E', $validationLastRow);
                $this->addInlineDropdown($sheet, 'F', array_values(config('variables.KEY_POSITIONS', [])), $validationLastRow);
                $this->addInlineDropdown($sheet, 'G', range(0, 10), $validationLastRow);
                $this->addInlineDropdown($sheet, 'H', range(0, 10), $validationLastRow);
                $this->addInlineDropdown($sheet, 'I', range(0, 10), $validationLastRow);
                $this->addInlineDropdown($sheet, 'J', [0, 1, 2], $validationLastRow);
                $this->addInlineDropdown($sheet, 'K', [0, 1], $validationLastRow);
                $this->addInlineDropdown($sheet, 'L', range(1, 5), $validationLastRow);

                $range = 'A1:'.$lastColumn.$lastRow;

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '#000000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
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
        $sheet->getColumnDimension('M')->setAutoSize(true);
    }

    private function addInlineDropdown(Worksheet $sheet, string $cell, array $options, int $lastRow): void
    {
        $formula = '"'.implode(',', $options).'"';

        $validation = new DataValidation;
        $this->configureListValidation($validation, $formula);
        $sheet->setDataValidation($cell.'2:'.$cell.$lastRow, $validation);
    }

    private function configureListValidation(DataValidation $validation, string $formula): void
    {
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowDropDown(true);
        $validation->setPromptTitle('Seleccione una opción.');
        $validation->setPrompt('Por favor seleccione un elemento de la lista.');
        $validation->setErrorTitle('Error');
        $validation->setError('El valor no está en la lista.');
        $validation->setFormula1($formula);
    }

    private function addDropdownMin(Worksheet $sheet, string $cell, int $lastRow): void
    {
        for ($i = 2; $i <= $lastRow; $i++) {
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
