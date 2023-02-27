<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionGroup;
use App\Imports\ImportMatchDetail;
use App\Repositories\GameRepository;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\AssistRepository;
use App\Repositories\PlayerRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InscriptionRepository;

class ImportController extends Controller
{

    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private AssistRepository $assistRepository,
        private IncidentRepository $incidentRepository,
        private GameRepository $gameRepository,
        private PlayerRepository $playerRepository
    ){}

    public function importMatchDetail(Request $request)
    {
        $file = $request->file('file');

        $importMatchDetail = new ImportMatchDetail();
        
        Excel::import($importMatchDetail, $file);

        $response = $this->gameRepository->loadDataFromFile($importMatchDetail->getData());

        return response()->json($response);

    }


}