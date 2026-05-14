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
use App\Models\Player;
use App\Models\UniformRequest;
use App\Traits\PDFTrait;
use App\Traits\UploadFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceRepository
{
    use UploadFile;
    use PDFTrait;

    public function invoicesPlayer()
    {
        $player = request()->user();
        $player->load(['inscription.invoices.items']);
        return $player->inscription->invoices;
    }

    public function invoicesForPlayers($players)
    {
        $inscriptionIds = $players->pluck('inscription.id')->filter()->values();

        return Invoice::query()
            ->with(['items', 'inscription.player'])
            ->whereIn('inscription_id', $inscriptionIds)
            ->latest('id')
            ->get();
    }

    public function statisticsPlayer()
    {
        $player = request()->user();
        $player->load(['inscription.invoices.items']);
        return data_get($player->inscription, 'invoices', collect());
    }

    public function statisticsForPlayers($players)
    {
        return $this->invoicesForPlayers($players);
    }

    public function findPlayerInvoiceOrFail(int $invoiceId): Invoice
    {
        /** @var Player $player */
        $player = request()->user();
        $player->loadMissing('inscription');

        $inscriptionId = $player->inscription?->id;

        if (!$inscriptionId) {
            throw new ModelNotFoundException();
        }

        return Invoice::query()
            ->with('items')
            ->whereKey($invoiceId)
            ->where('inscription_id', $inscriptionId)
            ->firstOrFail();
    }

    public function findPlayersInvoiceOrFail($players, int $invoiceId): Invoice
    {
        $inscriptionIds = $players->pluck('inscription.id')->filter()->values();

        if ($inscriptionIds->isEmpty()) {
            throw new ModelNotFoundException();
        }

        return Invoice::query()
            ->with(['items', 'inscription.player'])
            ->whereKey($invoiceId)
            ->whereIn('inscription_id', $inscriptionIds)
            ->firstOrFail();
    }

    public function query(): Builder
    {
        return Invoice::with(['inscription.player', 'trainingGroup'])
            ->schoolId();
    }

    public function makeInvoice($inscriptionId, $school)
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
        if ($payment) {
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
        }

        if ($payment) {
            foreach ($months as $key => $name) {
                if (in_array($payment->{$key}, [2])) { //2 = debe
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

    public function createInvoice($inscriptionId)
    {
        $school = getSchool(auth()->user());

        [$inscription, $pendingMonths] = $this->makeInvoice($inscriptionId, $school);

        $pendingUniformRequests = $this->addUniformRequest($inscription->player_id);
        $customCharges = $this->customChargesForInvoice($inscription);

        return [$inscription, $pendingMonths, $pendingUniformRequests, $customCharges];
    }

    public function storeInvoice(array $validated): ?Int
    {
        try {
            return DB::transaction(function () use ($validated) {
                // Generar número de factura único
                $invoiceNumber = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('Ymd');

                // Verificar que no exista
                while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                    $invoiceNumber = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('Ymd');
                }

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'inscription_id' => $validated['inscription_id'],
                    'training_group_id' => $validated['training_group_id'],
                    'year' => $validated['year'],
                    'student_name' => $validated['student_name'],
                    'issue_date' => now()->toDateString(),
                    'due_date' => $validated['due_date'],
                    'status' => 'pending',
                    'school_id' =>  $validated['school_id'],
                    'created_by' => auth()->id(),
                    'notes' => $validated['notes'],
                ]);

                $items = [];
                foreach ($validated['items'] as $itemData) {
                    $quantity = (int) $itemData['quantity'];
                    $unitPrice = (float) $itemData['unit_price'];
                    $customChargeId = $itemData['custom_charge_id'] ?? null;

                    if (! empty($customChargeId)) {
                        InscriptionCustomCharge::query()
                            ->whereKey($customChargeId)
                            ->where('school_id', $validated['school_id'])
                            ->where('inscription_id', $validated['inscription_id'])
                            ->where('status', InscriptionCustomCharge::STATUS_DUE)
                            ->whereNull('invoice_item_id')
                            ->lockForUpdate()
                            ->firstOrFail();
                    }

                    $items[] = [
                        'type' => $itemData['type'],
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => round($quantity * $unitPrice, 2),
                        'month' => $itemData['month'] ?? null,
                        'payment_id' => $itemData['payment_id'] ?? null,
                        'is_paid' => false,
                        'uniform_request_id' => $itemData['uniform_request_id'] ?? null,
                        'custom_charge_id' => $customChargeId,
                    ];

                    if (!empty($itemData['uniform_request_id'])) {
                        UniformRequest::where('id', $itemData['uniform_request_id'])->update(['status' => 'APPROVED']);
                    }
                }

                InvoiceItem::withoutEvents(function () use ($invoice, $items): void {
                    foreach ($items as $item) {
                        $invoiceItem = $invoice->items()->create($item);

                        if (! empty($item['custom_charge_id'])) {
                            InscriptionCustomCharge::query()
                                ->whereKey($item['custom_charge_id'])
                                ->whereNull('invoice_item_id')
                                ->update(['invoice_item_id' => $invoiceItem->id]);
                        }
                    }
                });

                $invoice->updateTotals();

                return $invoice->id;
            });
        } catch (\Throwable $th) {
            report($th);
            return null;
        }
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $invoice = Invoice::query()->schoolId()->findOrFail($invoiceId);

        $invoice->issue_date = $request->validated('issue_date');

        $paymentReceived = PaymentReceived::query()->create([
            'invoice_id' => $invoiceId,
            'amount' => $request->validated('amount'),
            'payment_method' => $request->validated('payment_method'),
            'reference' => $request->validated('reference'),
            'payment_date' => $request->validated('payment_date'),
            'notes' => $request->validated('notes'),
            'school_id' => $invoice->school_id,
            'created_by' => auth()->id(),
        ]);

        // Actualizar totales de la factura
        $invoice->updateTotals();

        // Si el usuario seleccionó ítems específicos para marcar como pagados
        if ($request->has('paid_items')) {
            $paidItemIds = collect($request->validated('paid_items'))
                ->filter()
                ->map(static fn ($itemId) => (int) $itemId)
                ->unique()
                ->values();

            if ($paidItemIds->isNotEmpty()) {
                $invoice->items()
                    ->whereIn('id', $paidItemIds->all())
                    ->update([
                        'is_paid' => true,
                        'payment_received_id' => $paymentReceived->id,
                    ]);
            }

            // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
            $invoice->markMonthsAsPaid();
            $invoice->markCustomChargesAsPaid();
        }
    }

    public function addPaymentButton($invoiceId, $paymentRequestId)
    {
        $invoice = Invoice::query()->schoolId()->findOrFail($invoiceId);
        $paymentRequest = PaymentRequest::query()
            ->schoolId()
            ->where('invoice_id', $invoice->id)
            ->findOrFail($paymentRequestId);

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
        // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
        $invoice->markMonthsAsPaid();
        $invoice->markCustomChargesAsPaid();
    }

    public function getAllItems()
    {
        return InvoiceItem::query()->select(['invoice_items.*', 'payments_received.payment_method'])->with('paymentReceived')
            ->withWhereHas('invoice', fn($q) => $q->schoolId())
            ->leftJoin('payments_received', 'invoice_items.payment_received_id', 'payments_received.id');
    }

    private function addUniformRequest($playerId)
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
        ->schoolId()
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

    private function customChargesForInvoice(Inscription $inscription)
    {
        return $inscription->customCharges()
            ->where('status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('invoice_item_id')
            ->get()
            ->map(fn (InscriptionCustomCharge $charge) => [
                'id' => $charge->id,
                'description' => $charge->name,
                'quantity' => 1,
                'unit_price' => (float) $charge->value,
                'status' => $charge->status,
                'due_date' => optional($charge->due_date)->toDateString(),
                'custom_charge_id' => $charge->id,
            ]);
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
}
