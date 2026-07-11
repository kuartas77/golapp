<?php

namespace App\Service\Import;

use App\Imports\ImportMatchDetail;
use App\Imports\ImportPlayers;
use App\Repositories\GameRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ImportService
{
    public function __construct(private GameRepository $games, private PlayerRepository $players, private InscriptionRepository $inscriptions) {}

    public function matchDetail(UploadedFile $file): array
    {
        $import = new ImportMatchDetail();
        Excel::import($import, $file);
        return $this->games->loadDataFromFile($import->getData());
    }

    public function players(UploadedFile $file, int $schoolId): array
    {
        $diff = $this->players->validateImport($file);
        if ($diff !== '') {
            throw ValidationException::withMessages(['file' => "Error en las columnas: {$diff}"]);
        }
        $import = new ImportPlayers($schoolId, $this->players, $this->inscriptions);
        Excel::import($import, $file);
        return $import->summary();
    }
}
