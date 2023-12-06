<?php

namespace App\Exports;

use App\Repositories\GameRepository;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MatchDetailExport implements FromView, WithTitle, WithStyles
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

    public function styles(Worksheet $sheet)
    {
        $positions = implode(',', config('variables.KEY_POSITIONS'));
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $sheet->setCellValue('M22', "Los campos Asistio, Titular se deben llenar con Si, No.
        El compo de Jugó Aprox se debe llenar sólo con números.
        El compo de Posicion se debe llenar con la información siguiente:
        Posiciones: {$positions}")->mergeCells('M22:R33');
        $sheet->getStyle('M22:R33')->getFont()->setBold(true);
        $sheet->getStyle('M22:R33')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M22:R33')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('M22:R33')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('O')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setAutoSize(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        $sheet->getColumnDimension('R')->setAutoSize(true);
    }
}
