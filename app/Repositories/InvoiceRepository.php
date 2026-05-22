<?php

namespace App\Repositories;

use App\Http\Requests\InvoiceAddPaymentRequest;
use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentReceived;
use App\Models\PaymentRequest;
use App\Models\School;
use App\Models\UniformRequest;
use App\Traits\ErrorTrait;
use App\Traits\PDFTrait;
use App\Traits\UploadFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceRepository
{
    use ErrorTrait;
    use UploadFile;
    use PDFTrait;

    public function invoicesPlayer()
    {
        $player = request()->user();
        $player->load(['inscription.invoices.items']);
        return $player->inscription->invoices;
    }

    public function statisticsPlayer()
    {
        $player = request()->user();
        $player->load(['inscription.invoices.items']);
        return data_get($player->inscription, 'invoices', collect());
    }

    public function query(): Builder
    {
        return Invoice::with(['inscription.player', 'trainingGroup'])
            ->schoolId();
    }

    public function makeInvoice(int $inscriptionId, School $school)
    {
        $inscription = Inscription::with(['player', 'trainingGroup'])
            ->where('school_id', $school->id)
            ->findOrFail($inscriptionId);

        // Buscar registro en payments para el año actual
        $currentYear = date('Y');
        $payment = Payment::query()->where('inscription_id', $inscriptionId)
            ->where('year', $currentYear)
            ->with('inscription:id,player_id')
            ->first();

        $pendingMonths = [];

        $months = [
            'enrollment' => 'Matrícula',
            'january' => 'Enero',
            'february' => 'Febrero',
            'march' => 'Marzo',
            'april' => 'Abril',
            'may' => 'Mayo',
            'june' => 'Junio',
            'july' => 'Julio',
            'august' => 'Agosto',
            'september' => 'Septiembre',
            'october' => 'Octubre',
            'november' => 'Noviembre',
            'december' => 'Diciembre'
        ];

        if ($payment) {
            foreach ($months as $key => $name) {
                if ($payment->{$key} == 2) { // 2 = debe
                    $pendingMonths[] = [
                        'month' => $key,
                        'name' => $name,
                        'amount' => $payment->{$key . '_amount'} ?? 0,
                        'payment_id' => $payment->id
                    ];
                }
            }
        }

        return [$inscription, $pendingMonths];
    }

    public function createInvoice(int $inscriptionId)
    {
        $school = getSchool(auth()->user());

        [$inscription, $pendingMonths] = $this->makeInvoice($inscriptionId, $school);

        $pendingUniformRequests = $this->addUniformRequest($inscription->player_id, $inscription->school_id);

        $customCharges = InscriptionCustomCharge::query()
            ->where('school_id', $school->id)
            ->where('inscription_id', $inscription->id)
            ->where('status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('invoice_item_id')
            ->orderBy('due_date')
            ->get();

        return [$inscription, $pendingMonths, $pendingUniformRequests, $customCharges];
    }

    public function storeInvoice(array $validated): array
    {
        try {
            return DB::transaction(function () use ($validated) {
                $idempotencyKey = $validated['idempotency_key'] ?? null;

                if ($idempotencyKey) {
                    $existingInvoiceId = Invoice::query()
                        ->where('idempotency_key', $idempotencyKey)
                        ->value('id');

                    if ($existingInvoiceId) {
                        return [
                            'id' => $existingInvoiceId,
                            'created' => false,
                        ];
                    }
                }

                $invoiceNumber = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('Ymd');

                while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                    $invoiceNumber = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('Ymd');
                }

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'idempotency_key' => $idempotencyKey,
                    'inscription_id' => $validated['inscription_id'],
                    'training_group_id' => $validated['training_group_id'],
                    'year' => $validated['year'],
                    'student_name' => $validated['student_name'],
                    'issue_date' => now()->toDateString(),
                    'due_date' => $validated['due_date'],
                    'status' => 'pending',
                    'school_id' => $validated['school_id'],
                    'created_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $itemData) {
                    $item = [
                        'type' => $itemData['type'],
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'month' => $itemData['month'] ?? null,
                        'payment_id' => $itemData['payment_id'] ?? null,
                        'is_paid' => false,
                        'uniform_request_id' => $itemData['uniform_request_id'] ?? null,
                    ];

                    $invoiceItem = $invoice->items()->create($item);

                    if (!empty($itemData['custom_charge_id'])) {
                        $updatedCharge = InscriptionCustomCharge::query()
                            ->where('school_id', $invoice->school_id)
                            ->where('inscription_id', $invoice->inscription_id)
                            ->where('status', InscriptionCustomCharge::STATUS_DUE)
                            ->whereNull('invoice_item_id')
                            ->whereKey($itemData['custom_charge_id'])
                            ->update([
                                'invoice_item_id' => $invoiceItem->id,
                            ]);

                        throw_if(
                            $updatedCharge === 0,
                            \RuntimeException::class,
                            'Custom charge not available for invoice.'
                        );
                    }

                    if (!empty($itemData['uniform_request_id'])) {
                        $updatedUniform = UniformRequest::query()
                            ->whereKey($itemData['uniform_request_id'])
                            ->update([
                                'status' => 'APPROVED',
                            ]);

                        throw_if(
                            $updatedUniform === 0,
                            \RuntimeException::class,
                            'Uniform request not available for invoice.'
                        );
                    }
                }

                return [
                    'id' => $invoice->id,
                    'created' => true,
                ];
            });
        } catch (\Throwable $th) {
            $this->logError('InvoiceRepository@storeInvoice', $th);
            return [
                'id' => null,
                'created' => false,
            ];
        }
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $validated = $request->validated();

        DB::transaction(function () use ($invoice, $invoiceId, $validated) {
            $invoice->issue_date = $validated['issue_date'];

            $paymentReceived = PaymentReceived::query()->create([
                'invoice_id' => $invoiceId,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
                'school_id' => $validated['school_id'],
                'created_by' => auth()->id(),
            ]);

            // Actualizar totales de la factura y persistir la fecha de emisión editada.
            $invoice->updateTotals();

            foreach ($validated['paid_items'] as $itemId) {
                $item = $invoice->items()->find($itemId);
                if ($item) {
                    $item->update(['is_paid' => true, 'payment_received_id' => $paymentReceived->id]);
                }
            }

            $this->markCustomChargesAsPaid($invoice, $validated['paid_items']);

            // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
            $invoice->markMonthsAsPaid();
        });
    }

    public function addPaymentButton($invoiceId, $paymentRequestId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $paymentRequest = PaymentRequest::findOrFail($paymentRequestId);

        $paymentReceived = PaymentReceived::query()->create([
            'invoice_id' => $invoiceId,
            'amount' => $invoice->total_amount,
            'payment_method' => $paymentRequest->payment_method,
            'reference' => $paymentRequest->reference_number,
            'payment_date' => now(),
            'notes' => $paymentRequest->description,
            'school_id' => $invoice->school_id,
            'created_by' => auth()->id(),
        ]);

        // Actualizar totales de la factura
        $invoice->updateTotals();

        $invoice->items()->update(['is_paid' => true, 'payment_received_id' => $paymentReceived->id]);
        $this->markCustomChargesAsPaid($invoice, $invoice->items()->pluck('id')->all());
        // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
        $invoice->markMonthsAsPaid();
    }

    public function getAllItems()
    {
        return InvoiceItem::query()->select(['invoice_items.*', 'payments_received.payment_method'])->with('paymentReceived')
            ->withWhereHas('invoice', fn($q) => $q->schoolId())
            ->leftJoin('payments_received', 'invoice_items.payment_received_id', 'payments_received.id');
    }

    public function addUniformRequest(int $playerId, int $schoolId)
    {
        $uniformRequests = UniformRequest::query()
        ->leftJoin('invoice_custom_items as ici', function ($join) {
            $join->on('ici.type', '=', 'uniform_request.type')
                ->on('ici.school_id', '=', 'uniform_request.school_id')
                ->whereNull('ici.deleted_at')
                // evita que un request OTHER intente matchear con items
                ->where('uniform_request.type', '!=', 'OTHER')
                // evita “usar” item OTHER (pueden existir muchos)
                ->where('ici.type', '!=', 'OTHER');
        })
        ->where('uniform_request.player_id', $playerId)
        ->where('uniform_request.status', 'PENDING')
        ->where('uniform_request.type', '!=', 'OTHER') // si quieres excluir OTHER del cálculo
        ->where('uniform_request.school_id', $schoolId)
        ->select([
            'uniform_request.*',
            DB::raw('COALESCE(ici.unit_price, 0) as unit_price'),
            DB::raw('ici.name as item_name'),
            DB::raw('ici.id as custom_id'),
        ])
        ->get();

        $uniformRequestsOthers = UniformRequest::query()
            ->where('player_id', $playerId)
            ->where('type', 'OTHER')
            ->get();

        $uniformRequests = $uniformRequests->merge($uniformRequestsOthers);

        $pendingUniformRequests = [];
        if($uniformRequests->isNotEmpty()) {
            $UNIFORM_REQUESTS_TYPES = config('variables.UNIFORM_REQUESTS_TYPES');
            foreach ($uniformRequests as $uniformRequests) {

                $size = $uniformRequests->size ? "Talla: {$uniformRequests->size}": '';

                if(!isset($uniformRequests->item_name) && isset($UNIFORM_REQUESTS_TYPES[$uniformRequests->type])) {
                    if($uniformRequests->type === 'OTHER') {
                        $type = trim("{$uniformRequests->additional_notes}");
                    }else {
                        $type = $UNIFORM_REQUESTS_TYPES[$uniformRequests->type];
                        $type = trim("{$type}: {$size} {$uniformRequests->additional_notes}");
                    }

                }else{
                    $type = trim("{$uniformRequests->item_name} {$size} {$uniformRequests->additional_notes}");
                }

                $pendingUniformRequests[] = [
                    'description' => $type,
                    'uniform_request_id' => $uniformRequests->id,
                    'quantity' => $uniformRequests->quantity,
                    'unit_price' => intval($uniformRequests->unit_price)
                ];
            }
        }

        return $pendingUniformRequests;
    }

    private function markCustomChargesAsPaid(Invoice $invoice, array $invoiceItemIds): void
    {
        $invoiceItemIds = collect($invoiceItemIds)->filter()->unique()->all();

        if (empty($invoiceItemIds)) {
            return;
        }

        InscriptionCustomCharge::query()
            ->where('school_id', $invoice->school_id)
            ->where('inscription_id', $invoice->inscription_id)
            ->whereIn('invoice_item_id', $invoiceItemIds)
            ->update(['status' => InscriptionCustomCharge::STATUS_PAID]);
    }

    public function exportPendingItems(bool $stream = true)
    {
        $items = $this->getAllItems()->where('is_paid', false)->get();

        $date = now()->format('d-m-Y H:i:s');
        $data = [];
        $data['school'] = getSchool(auth()->user());
        $data['items'] = $items;
        $data['date'] = $date;

        $filename = "Items de factura pendientes {$date}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'items-invoices.blade.php');
        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    public function buildAutoInvoiceIdempotencyKey(array $invoiceData, $currentDate): string
    {
        $items = collect($invoiceData['items'])
            ->map(function ($item) {
                return [
                    'type' => $item['type'] ?? null,
                    'month' => $item['month'] ?? null,
                    'payment_id' => $item['payment_id'] ?? null,
                    'uniform_request_id' => $item['uniform_request_id'] ?? null,
                    'custom_charge_id' => $item['custom_charge_id'] ?? null,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'unit_price' => number_format((float) ($item['unit_price'] ?? 0), 2, '.', ''),
                ];
            })
            ->sortBy(fn ($item) => json_encode($item))
            ->values()
            ->all();

        return hash('sha256', json_encode([
            'source' => 'automatic_invoice',
            'school_id' => (int) $invoiceData['school_id'],
            'inscription_id' => (int) $invoiceData['inscription_id'],
            'training_group_id' => (int) $invoiceData['training_group_id'],
            'year' => (int) $invoiceData['year'],
            'period' => $currentDate->format('Y-m'),
            'items' => $items,
        ]));
    }
}
