<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class InscriptionExport implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    public Collection $players;
    public bool $trash;

    public function __construct(Collection $players, bool $trash = false)
    {
        $this->players = $players;
        $this->trash = $trash;
    }

    public function view(): View
    {
        return view('exports.inscriptions_excel', [
            'players' => $this->players,
        ]);
    }

    public function title(): string
    {
        return $this->trash ? "Inactivos" : "Activos";
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
            }
        ];
    }
}
