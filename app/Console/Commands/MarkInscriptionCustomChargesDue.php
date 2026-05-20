<?php

namespace App\Console\Commands;

use App\Models\InscriptionCustomCharge;
use Illuminate\Console\Command;

class MarkInscriptionCustomChargesDue extends Command
{
    protected $signature = 'charges:mark-due';

    protected $description = 'Mark pending inscription custom charges as due when their due date has passed';

    public function handle(): int
    {
        $updated = InscriptionCustomCharge::query()
            ->where('status', InscriptionCustomCharge::STATUS_PENDING)
            ->whereDate('due_date', '<=', now()->toDateString())
            ->update(['status' => InscriptionCustomCharge::STATUS_DUE]);

        $this->info("Cargos personalizados actualizados: {$updated}");

        return self::SUCCESS;
    }
}
