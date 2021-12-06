<?php

namespace App\Http\Controllers;

use App\Exports\AssistExport;
use App\Exports\InscriptionSheetsExport;
use App\Exports\PaymentsExport;
use App\Models\Player;
use App\Repositories\AssistRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\GameRepository;
use App\Repositories\PlayerRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    
    private InscriptionRepository $inscriptionRepository;
    
    private AssistRepository $assistRepository;

    private IncidentRepository $incidentRepository;

    private GameRepository $gameRepository;

    private PlayerRepository $playerRepository;

    public function __construct(InscriptionRepository $inscriptionRepository,
                                AssistRepository $assistRepository,
                                IncidentRepository $incidentRepository,
                                GameRepository $gameRepository,
                                PlayerRepository $playerRepository)
    {
        $this->inscriptionRepository = $inscriptionRepository;
        $this->assistRepository = $assistRepository;
        $this->incidentRepository = $incidentRepository;
        $this->gameRepository = $gameRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function exportPlayerPDF(Player $player)
    {
        return $this->playerRepository->makePdf($player);
    }

    /**
     * @return BinaryFileResponse
     */
    public function exportInscriptionsExcel(): BinaryFileResponse
    {
        $date = now()->timestamp;
        return Excel::download(new InscriptionSheetsExport($this->playerRepository->getExcel()), "Inscripciones {$date}.xlsx");
    }

    /**
     * @param $trainingGroupId
     * @param $year
     * @param $month
     * @param bool $deleted
     * @return BinaryFileResponse
     */
    public function exportAssistsExcel($trainingGroupId, $year, $month, bool $deleted = false): BinaryFileResponse
    {
        $params = [
            'training_group_id' => $trainingGroupId,
            'year' => $year,
            'month' => $month
        ];
        $date = now()->timestamp;
        return Excel::download(new AssistExport($params, $deleted), "Asistencias {$date}.xlsx");
    }

    /**
     * @param $trainingGroupId
     * @param $year
     * @param $month
     * @param bool $deleted
     * @return mixed
     * @throws \Mpdf\MpdfException
     */
    public function exportAssistsPDF($trainingGroupId, $year, $month, $deleted = false)
    {
        $params = [
            'training_group_id' => $trainingGroupId,
            'year' => $year,
            'month' => $month
        ];
        return $this->assistRepository->generatePDF($params, $deleted);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportPaymentsExcel(Request $request): BinaryFileResponse
    {
        $date = now()->timestamp;
        return Excel::download(new PaymentsExport($request, $request->input('deleted', false)), "Pagos {$date}.xlsx");
    }


    public function exportMatchPDF($match)
    {
        return $this->gameRepository->makePDF($match);
    }

    public function exportIncidentsPDF($slug_name)
    {
        $incidents = $this->incidentRepository->get($slug_name);
        $professor = $incidents->first()->professor;

        view()->share('incidents', $incidents);
        view()->share('professor', $professor);
        //return view('exports.incidents_pdf');
        //$pdf = PDF::loadView('exports.incidents_pdf');
        //return $pdf->stream("Incidencias");
    }
}
