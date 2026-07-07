<?php

declare(strict_types=1);

namespace App\Service\Player;

use App\Models\Player;
use App\Models\School;
use App\Service\Reports\DebtorReportService;
use App\Traits\PDFTrait;
use Illuminate\Support\Carbon;

final class PlayerFinancialClearanceService
{
    use PDFTrait;

    public function __construct(private DebtorReportService $debtorReportService) {}

    public function status(School $school, Player $player, ?Carbon $asOf = null): array
    {
        $asOf ??= now();
        $debts = $this->debtorReportService->playerDebts($school->id, $player->id, $asOf);

        return [
            'eligible' => $debts->isEmpty(),
            'as_of' => $asOf->toIso8601String(),
            'debts' => $debts,
            'total_debt' => (float) $debts->sum('amount'),
        ];
    }

    public function generatePdf(School $school, Player $player)
    {
        $issuedAt = now();
        $status = $this->status($school, $player, $issuedAt);

        if (! $status['eligible']) {
            return response()->json([
                'message' => 'El deportista tiene obligaciones financieras vencidas y no puede generar el paz y salvo.',
                ...$status,
            ], 422);
        }

        $this->setConfigurationMpdf([
            'format' => 'A4',
            'margin_left' => 14,
            'margin_right' => 14,
            'margin_top' => 12,
            'margin_bottom' => 14,
        ]);
        $this->createPDF([
            'school' => $school,
            'player' => $player,
            'issuedAt' => $issuedAt,
        ], 'player_financial_clearance.blade.php', showFooter: false, mark: false);

        $filename = 'Paz y salvo '.$player->unique_code.'.pdf';

        return $this->stream($filename);
    }
}
