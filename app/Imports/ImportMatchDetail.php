<?php

namespace App\Imports;

use App\Models\Inscription;
use App\Models\SkillsControl;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class ImportMatchDetail implements ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow, WithMultipleSheets, WithValidation
{
    private $data;

    public function __construct(private $matchId = null)
    {
        $this->data = collect();
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'El formato no contiene datos para importar.',
            ]);
        }

        $this->validateHeadings($rows->first());

        $codes = $rows
            ->pluck('codigo')
            ->filter(fn ($code) => filled($code))
            ->map(fn ($code) => trim((string) $code))
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'El formato no contiene códigos de deportistas.',
            ]);
        }

        $inscriptions = Inscription::query()->select(['id', 'player_id', 'unique_code'])
            ->with('player:id,names,last_names,unique_code')
            ->whereIn('unique_code', $codes)
            ->where('school_id', getSchool(auth()->user())->id)
            ->get();

        $inscriptions = $inscriptions->keyBy('unique_code');
        $missingCodes = $codes->diff($inscriptions->keys());

        if ($missingCodes->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'No se encontraron deportistas con estos códigos: '.$missingCodes->implode(', '),
            ]);
        }

        foreach ($rows as $row) {
            $code = trim((string) ($row['codigo'] ?? ''));

            if ($code !== '') {
                $inscription = $inscriptions[$code];

                $skillControll = new SkillsControl([
                    'game_id' => $this->matchId,
                    'inscription_id' => $inscription->id,
                    'assistance' => cleanString(strtolower($row['asistio'])) == 'si' ? 1 : 0,
                    'titular' => cleanString(strtolower($row['titular'])) == 'si' ? 1 : 0,
                    'played_approx' => intval($row['jugo_aprox']),
                    'position' => $row['posicion'],
                    'goals' => intval($row['goles']),
                    'goal_assists' => intval($row['asistencia_gol']),
                    'goal_saves' => intval($row['atajadas']),
                    'red_cards' => intval($row['rojas']),
                    'yellow_cards' => intval($row['amarillas']),
                    'qualification' => intval($row['calificacion']),
                    'observation' => $row['observacion'] ?? null,
                ]);

                if ($this->matchId) {
                    $skillControll->save();
                }

                $skillControll->inscription = $inscription;

                $this->data->push($skillControll);
            }
        }
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
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

    private function validateHeadings($row): void
    {
        $requiredHeadings = [
            'deportista',
            'codigo',
            'asistio',
            'titular',
            'jugo_aprox',
            'posicion',
            'goles',
            'asistencia_gol',
            'atajadas',
            'amarillas',
            'rojas',
            'calificacion',
            'observacion',
        ];

        $missingHeadings = collect($requiredHeadings)
            ->diff(collect($row?->keys() ?? []))
            ->values();

        if ($missingHeadings->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'El formato no tiene las columnas requeridas: '.$missingHeadings->implode(', '),
            ]);
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
