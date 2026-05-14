<?php

namespace App\Console\Commands;

use App\Models\InscriptionCustomCharge;
use Illuminate\Console\Command;

class MarkCustomChargesDue extends Command
{
    protected $signature = 'charges:mark-due';

    protected $description = 'Mark expired inscription custom charges as due';

    public function handle(): int
    {
        $affected = InscriptionCustomCharge::query()
            ->where('status', InscriptionCustomCharge::STATUS_PENDING)
            ->whereNull('invoice_item_id')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => InscriptionCustomCharge::STATUS_DUE]);

        $this->info("Cargos vencidos actualizados: {$affected}");

        return self::SUCCESS;
    }
}
