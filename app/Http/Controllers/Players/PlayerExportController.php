<?php

namespace App\Http\Controllers\Players;

use App\Models\Player;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InscriptionSheetsExport;
use App\Service\Player\PlayerExportService;
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

    public function exportInscription($player_id, $inscription_id, PlayerExportService $playerExportService)
    {
        $playerExportService->makePDFInscriptionDetail($player_id, $inscription_id);
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
