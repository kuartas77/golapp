<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceRowsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $rows
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows)->map(function (array $row) {
            return collect($row)->values();
        });
    }

    public function headings(): array
    {
        return array_keys($this->rows[0] ?? []);
    }
}