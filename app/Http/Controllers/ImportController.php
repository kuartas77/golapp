<?php

namespace App\Http\Controllers;

use App\Imports\ImportMatchDetail;
use App\Imports\ImportPlayers;
use App\Repositories\AssistRepository;
use App\Repositories\GameRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportController extends Controller
{
    use ErrorTrait;

    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private AssistRepository      $assistRepository,
        private IncidentRepository    $incidentRepository,
        private GameRepository        $gameRepository,
        private PlayerRepository      $playerRepository
    )
    {
    }

    public function importMatchDetail(Request $request)
    {
        $file = $request->file('file');

        $importMatchDetail = new ImportMatchDetail($request->match);

        Excel::import($importMatchDetail, $file);

        $response = $this->gameRepository->loadDataFromFile($importMatchDetail->getData());

        return response()->json($response);

    }

    public function importPlayers(Request $request, PlayerRepository $playerRepository)
    {
        try {

            $diff = $this->playerRepository->validateImport($request->file('file'));
            if ($diff !== "") {

                alert()->error("Error en las columnas a importar",
                    "Error en las columnas: {$diff}");
                return back();
            }

            $importPlayers = new ImportPlayers($request->school_id, $playerRepository);
            Excel::import($importPlayers, $request->file('file'));

            alert()->success(env('APP_NAME'), __('messages.player_created'));

        } catch (Throwable $th) {
            $this->logError('importPlayers', $th);
            alert()->error(env('APP_NAME'), __('messages.error_general'));
        }

        return back();
    }
}
