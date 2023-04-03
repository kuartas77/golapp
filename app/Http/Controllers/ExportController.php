<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\AssistExport;
use App\Exports\PaymentsExport;
use App\Exports\MatchDetailExport;
use App\Repositories\GameRepository;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\AssistRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PaymentRepository;
use App\Service\Assist\AssistExportService;
use App\Service\Payment\PaymentExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{

    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private AssistRepository $assistRepository,
        private IncidentRepository $incidentRepository,
        private GameRepository $gameRepository,
        private PaymentRepository $paymentRepository,
    ){}


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
    public function exportAssistsPDF($trainingGroupId, $year, $month, $deleted = false, AssistExportService $assistExportService)
    {
        $params = [
            'training_group_id' => $trainingGroupId,
            'year' => $year,
            'month' => $month
        ];
        return $assistExportService->generatePDF($params, $deleted);
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

    public function exportPaymentsPDF(Request $request, PaymentExportService $paymentExportService)
    {
        $payments = $this->paymentRepository->filterSelect($request, false)->get();
        return $paymentExportService->paymentsPdfByGroup($payments, $request, true);
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

    public function exportMatchDetail($competition_group)
    {
        $date = now()->timestamp;
        return Excel::download(new MatchDetailExport($competition_group), "{$date}.xlsx");
    }
}
