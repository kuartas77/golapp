<?php

namespace App\Exports;

use App\Models\Tournament;
use Illuminate\Http\Request;
use App\Models\CompetitionGroup;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Repositories\TournamentPayoutsRepository;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class TournamentPayoutsExport implements FromView, WithTitle, WithColumnFormatting, ShouldAutoSize
{
    use Exportable;

    private $data;
    private $deleted;
    private $tournament;

    /**
     * TournamentPayoutsExport constructor.
     * @param Request $request
     * @param $deleted
     */
    public function __construct(array $data, $deleted)
    {
        $this->data = $data;
        $this->deleted = $deleted;
    }

    public function view(): View
    {
        $payments = app(TournamentPayoutsRepository::class)->filterSelect($this->data, $this->deleted);
        $this->tournament = Tournament::query()->find($this->data['tournament_id']);
        $group = CompetitionGroup::query()->without(['inscriptions'])->find($this->data['competition_group_id']);
        return view('exports.tournament_payouts_excel', [
            'payments' => $payments->get(),
            'tournament' => $this->tournament,
            'group' => $group
        ]);
    }

    public function title(): string
    {
        return "Pagos torneos";
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
        ];
    }
}
