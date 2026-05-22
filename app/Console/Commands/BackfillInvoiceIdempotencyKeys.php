<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackfillInvoiceIdempotencyKeys extends Command
{
    protected $signature = 'invoices:backfill-idempotency-keys
        {--dry-run : Show the changes without updating invoices}
        {--period= : Automatic invoice period to protect, in YYYY-MM format. Defaults to the current month}
        {--school-id=* : Limit the backfill to one or more school IDs}';

    protected $description = 'Fill missing invoice idempotency keys using the automatic invoice key format';

    public function __construct(private InvoiceRepository $repository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $periodDate = $this->periodDate();
        $schoolIds = collect($this->option('school-id'))
            ->filter(fn ($schoolId) => is_numeric($schoolId))
            ->map(fn ($schoolId) => (int) $schoolId)
            ->values();

        $updated = 0;
        $skippedDuplicates = 0;
        $skippedWithoutItems = 0;
        $seenKeys = [];

        $query = Invoice::query()
            ->with(['items' => fn ($query) => $query->orderBy('id')])
            ->whereNull('idempotency_key')
            ->orderBy('id');

        if ($schoolIds->isNotEmpty()) {
            $query->whereIn('school_id', $schoolIds);
        }

        $query->chunkById(100, function ($invoices) use (
            $dryRun,
            &$updated,
            &$skippedDuplicates,
            &$skippedWithoutItems,
            &$seenKeys,
            $periodDate
        ) {
            foreach ($invoices as $invoice) {
                if ($invoice->items->isEmpty()) {
                    $skippedWithoutItems++;
                    $this->warn("Factura {$invoice->id} omitida: no tiene items.");
                    continue;
                }

                $idempotencyKey = $this->repository->buildAutoInvoiceIdempotencyKey(
                    $this->invoicePayload($invoice),
                    $periodDate
                );

                if (isset($seenKeys[$idempotencyKey])) {
                    $skippedDuplicates++;
                    $this->warn(
                        "Factura {$invoice->id} omitida: comparte llave con factura {$seenKeys[$idempotencyKey]}."
                    );
                    continue;
                }

                $existingInvoiceId = Invoice::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->value('id');

                if ($existingInvoiceId) {
                    $skippedDuplicates++;
                    $seenKeys[$idempotencyKey] = $existingInvoiceId;
                    $this->warn(
                        "Factura {$invoice->id} omitida: la llave ya existe en factura {$existingInvoiceId}."
                    );
                    continue;
                }

                $seenKeys[$idempotencyKey] = $invoice->id;

                if (! $dryRun) {
                    $invoice->forceFill(['idempotency_key' => $idempotencyKey])->save();
                }

                $updated++;
            }
        });

        $action = $dryRun ? 'calculadas' : 'actualizadas';
        $this->info("Periodo protegido: {$periodDate->format('Y-m')}");
        $this->info("Facturas {$action}: {$updated}");
        $this->info("Duplicados omitidos: {$skippedDuplicates}");
        $this->info("Sin items omitidas: {$skippedWithoutItems}");

        return self::SUCCESS;
    }

    private function periodDate(): Carbon
    {
        $period = $this->option('period');

        if (! $period) {
            return now()->startOfMonth();
        }

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            throw new \InvalidArgumentException('The --period option must use the YYYY-MM format.');
        }

        return Carbon::createFromFormat('Y-m-d', "{$period}-01")->startOfMonth();
    }

    private function invoicePayload(Invoice $invoice): array
    {
        return [
            'school_id' => $invoice->school_id,
            'inscription_id' => $invoice->inscription_id,
            'training_group_id' => $invoice->training_group_id,
            'year' => $invoice->year,
            'items' => $invoice->items->map(fn ($item) => [
                'type' => $item->type,
                'month' => $item->month,
                'payment_id' => $item->payment_id,
                'uniform_request_id' => $item->uniform_request_id,
                'custom_charge_id' => $item->custom_charge_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->all(),
        ];
    }
}
