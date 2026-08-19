<?php

namespace App\Exports;

use App\Models\Player;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InscriptionSheetsExport implements WithMultipleSheets
{
    use Exportable;

    /** @var Collection<string, EloquentCollection<int, Player>> */
    private Collection $players;

    /**
     * @param Collection<string, EloquentCollection<int, Player>> $data
     */
    public function __construct(Collection $data)
    {
        $this->players = $data;
    }

    /** @return array<int, InscriptionExport> */
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new InscriptionExport($this->players->get('enabled'));
        $sheets[] = new InscriptionExport($this->players->get('disabled'), trash: true);

        return $sheets;
    }
}
