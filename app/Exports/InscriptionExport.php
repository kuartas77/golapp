<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class InscriptionExport implements FromView, WithTitle, ShouldAutoSize
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
}
