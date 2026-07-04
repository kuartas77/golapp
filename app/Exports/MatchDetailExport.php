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
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class MatchDetailExport implements FromView, WithTitle, WithStyles , ShouldAutoSize, WithEvents
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
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                $validationLastRow = max($lastRow, 30);

                $this->addOptionsSheet($sheet);
                $this->addDropdownFromRange($sheet, "C", 'MATCH_YES_NO', $validationLastRow);
                $this->addDropdownFromRange($sheet, "D", 'MATCH_YES_NO', $validationLastRow);
                $this->addDropdownMin($sheet, "E", $validationLastRow);
                $this->addDropdownFromRange($sheet, "F", 'MATCH_POSITIONS', $validationLastRow);
                $this->addDropdownFromRange($sheet, "G", 'MATCH_SCORE_0_10', $validationLastRow);
                $this->addDropdownFromRange($sheet, "H", 'MATCH_SCORE_0_10', $validationLastRow);
                $this->addDropdownFromRange($sheet, "I", 'MATCH_SCORE_0_10', $validationLastRow);
                $this->addDropdownFromRange($sheet, "J", 'MATCH_YELLOW_CARDS', $validationLastRow);
                $this->addDropdownFromRange($sheet, "K", 'MATCH_RED_CARDS', $validationLastRow);
                $this->addDropdownFromRange($sheet, "L", 'MATCH_QUALIFICATIONS', $validationLastRow);

                $range = 'A1:' . $lastColumn . $lastRow;

                $sheet->getStyle($range)->applyFromArray([
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

    private function addOptionsSheet(Worksheet $sheet): void
    {
        $spreadsheet = $sheet->getParent();
        $optionsSheet = $spreadsheet->getSheetByName('Opciones');

        if (! $optionsSheet) {
            $optionsSheet = $spreadsheet->createSheet();
            $optionsSheet->setTitle('Opciones');
        }

        $this->addOptionsRange($optionsSheet, 'A', 'MATCH_YES_NO', ['Sí', 'No']);
        $this->addOptionsRange($optionsSheet, 'B', 'MATCH_POSITIONS', array_values(config('variables.KEY_POSITIONS', [])));
        $this->addOptionsRange($optionsSheet, 'C', 'MATCH_SCORE_0_10', range(0, 10));
        $this->addOptionsRange($optionsSheet, 'D', 'MATCH_YELLOW_CARDS', [0, 1, 2]);
        $this->addOptionsRange($optionsSheet, 'E', 'MATCH_RED_CARDS', [0, 1]);
        $this->addOptionsRange($optionsSheet, 'F', 'MATCH_QUALIFICATIONS', range(1, 5));

        $optionsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
    }

    private function addOptionsRange(Worksheet $optionsSheet, string $column, string $rangeName, array $options): void
    {
        foreach (array_values($options) as $index => $option) {
            $optionsSheet->setCellValue($column . ($index + 1), $option);
        }

        $spreadsheet = $optionsSheet->getParent();
        if ($spreadsheet->getNamedRange($rangeName)) {
            return;
        }

        $spreadsheet->addNamedRange(new NamedRange(
            $rangeName,
            $optionsSheet,
            '$' . $column . '$1:$' . $column . '$' . max(1, count($options))
        ));
    }

    private function addDropdownFromRange(Worksheet $sheet, string $cell, string $rangeName, int $lastRow): void
    {
        for ($i=2; $i <= $lastRow; $i++) {
            $this->configureListValidation(
                $sheet->getCell($cell.$i)->getDataValidation(),
                '=' . $rangeName
            );
        }
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
        for ($i=2; $i <= $lastRow; $i++) {
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
