<?php

namespace App\Http\Controllers;

use App\Exports\AssistExport;
use App\Exports\MatchDetailExport;
use App\Exports\PaymentsExport;
use App\Exports\TournamentPayoutsExport;
use App\Repositories\AssistRepository;
use App\Repositories\GameRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\TournamentPayoutsRepository;
use App\Service\Assist\AssistExportService;
use App\Service\Payment\PaymentExportService;
use App\Service\TrainigSession\TrainingSessionExportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\MpdfException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{

    public function __construct(
        private InscriptionRepository       $inscriptionRepository,
        private AssistRepository            $assistRepository,
        private IncidentRepository          $incidentRepository,
        private GameRepository              $gameRepository,
        private PaymentRepository           $paymentRepository,
        private TournamentPayoutsRepository $tournamentPayoutsRepository,
        private InvoiceRepository $invoiceRepository
    )
    {
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
     * @throws MpdfException
     */
    public function exportAssistsPDF(AssistExportService $assistExportService, $trainingGroupId, $year, $month, $deleted = false)
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
        $request->merge(['school_id' => getSchool(auth()->user())->id]);
        return Excel::download(new PaymentsExport($request->all(), $request->input('deleted', false)), "Pagos {$date}.xlsx");
    }

    public function exportPaymentsPDF(Request $request, PaymentExportService $paymentExportService)
    {
        $request->merge(['school_id' => getSchool(auth()->user())->id]);
        $payments = $this->paymentRepository->filterSelect($request->all(), false)->get();
        return $paymentExportService->paymentsPdfByGroup($payments, $request, true);
    }

    public function exportTournamentPayoutsExcel(Request $request): BinaryFileResponse
    {
        $date = now()->timestamp;
        $requestArray = $request->only(['tournament_id', 'competition_group_id', 'year', 'unique_code']);
        return Excel::download(new TournamentPayoutsExport($requestArray, $request->input('deleted', false)), "Pagos {$date}.xlsx");
    }

    public function exportTournamentPayoutsPDF(Request $request, PaymentExportService $paymentExportService)
    {
        $requestArray = $request->only(['tournament_id', 'competition_group_id', 'year', 'unique_code']);
        $payments = $this->tournamentPayoutsRepository->filterSelect($requestArray, false);
        return $paymentExportService->tournamentPayoutsPdfByGroup($payments->get(), $requestArray, true);
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
        return Excel::download(new MatchDetailExport($competition_group), "Control de competencia {$date}.xlsx");
    }

    public function exportTrainingSession(Request $request, int $id, TrainingSessionExportService $trainingSessionExportService,)
    {
        return $trainingSessionExportService->exportSessionPDF($id);
    }

    public function exportPendingItemsInvoices()
    {
        return $this->invoiceRepository->exportPendingItems();
    }
}
