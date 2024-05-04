<?php

namespace App\Http\Controllers\Players;

use App\Exports\InscriptionSheetsExport;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Service\Player\PlayerExportService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlayerExportController extends Controller
{
    /**
     * @param Player $player
     * @return mixed
     * @throws MpdfException
     */
    public function exportPlayerPDF(Player $player, PlayerExportService $playerExportService): mixed
    {
        return $playerExportService->makePDFPlayer($player);
    }

    public function exportInscription(PlayerExportService $playerExportService, $player_id, $inscription_id, $year = null, $quarter = '')
    {
        $playerExportService->makePDFInscriptionDetail($player_id, $inscription_id, $year, $quarter);
    }

    /**
     * @return BinaryFileResponse
     */
    public function exportInscriptionsExcel(PlayerExportService $playerExportService): BinaryFileResponse
    {
        $date = now()->timestamp;

        return Excel::download(new InscriptionSheetsExport($playerExportService->getExcel()), "Inscripciones {$date}.xlsx");
    }

}
