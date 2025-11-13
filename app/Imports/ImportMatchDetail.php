<?php

namespace App\Imports;

use App\Models\Inscription;
use App\Models\SkillsControl;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ImportMatchDetail implements ToCollection, WithValidation, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    private $data;

    public function __construct()
    {
        $this->data = collect();
    }

    public function collection(Collection $rows)
    {
        $inscriptions = Inscription::query()->select(['id', 'player_id','unique_code'])
            ->with('player:id,names,last_names,unique_code')
            ->whereIn('unique_code', $rows->pluck('codigo'))
            ->where('school_id', getSchool(auth()->user())->id)
            ->get();

        $inscriptions = $inscriptions->keyBy('unique_code');

        foreach ($rows as $row) {

            if ($row['codigo']) {
                $inscription = $inscriptions[$row['codigo']];

                $skillControll = new SkillsControl([
                    'inscription_id' => $inscription->id,
                    'assistance' => cleanString(strtolower($row['asistio'])) == 'si' ? 1 : 0,
                    'titular' => cleanString(strtolower($row['titular'])) == 'si' ? 1 : 0,
                    'played_approx' => intval($row['jugo_aprox']),
                    'position' => $row['posicion'],
                    'goals' => intval($row['goles']),
                    'red_cards' => intval($row['rojas']),
                    'yellow_cards' => intval($row['amarillas']),
                    'qualification' => intval($row['calificacion']),
                    'observation' => $row['observacion'],
                ]);

                $skillControll->inscription = $inscription;

                $this->data->push($skillControll);
            }
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function rules(): array
    {
        return [
            // 'administrador' => 'required|string',
        ];
    }

    public function getData()
    {
        return $this->data;
    }
}
