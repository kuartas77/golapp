<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InscriptionSheetsExport implements WithMultipleSheets
{
    use Exportable;

    private Collection $players;

    public function __construct(Collection $data)
    {
        $this->players = $data;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new InscriptionExport($this->players['enabled']);
        $sheets[] = new InscriptionExport($this->players['disabled'], trash: true);
        return $sheets;

    }
}
