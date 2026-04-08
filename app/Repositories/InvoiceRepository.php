<?php

namespace App\Repositories;

use App\Http\Requests\InvoiceAddPaymentRequest;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\InvoiceCustomItem;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentReceived;
use App\Models\PaymentRequest;
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
                'enrollment' => 'Inscripción',
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
                if (in_array($payment->{$key}, [0,2])) { // 0 = pendiente, 2 = debe
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

        [$pendingUniformRequests, $customItems] = $this->addUniformRequest($inscription->player_id);

        return [$inscription, $pendingMonths, $pendingUniformRequests, $customItems];
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

                    $invoice->items()->create($item);

                    if (isset($item['uniform_request_id'])) {
                        UniformRequest::where('id', $item['uniform_request_id'])->update(['status' => 'APPROVED']);
                    }
                }

                return $invoice->id;
            });
        } catch (\Throwable $th) {
            $this->logError('InvoiceRepository@storeInvoice', $th);
            return null;
        }
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $paymentReceived = PaymentReceived::query()->create([
            'invoice_id' => $invoiceId,
            'amount' => $request->validated('amount'),
            'payment_method' => $request->validated('payment_method'),
            'reference' => $request->validated('reference'),
            'payment_date' => $request->validated('payment_date'),
            'notes' => $request->validated('notes'),
            'school_id' => $request->validated('school_id'),
            'created_by' => auth()->id(),
        ]);

        // Actualizar totales de la factura
        $invoice->updateTotals();

        // Si el usuario seleccionó ítems específicos para marcar como pagados
        if ($request->has('paid_items')) {
            foreach ($request->validated('paid_items') as $itemId) {
                $item = $invoice->items()->find($itemId);
                if ($item) {
                    $item->update(['is_paid' => true, 'payment_received_id' => $paymentReceived->id]);
                }
            }

            // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
            $invoice->markMonthsAsPaid();
        }
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
        // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
        $invoice->markMonthsAsPaid();
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

        $custonIds = $uniformRequests->pluck('custom_id')->filter();

        $customItems = InvoiceCustomItem::query()
            ->when($custonIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $custonIds))
            ->schoolId()->get();

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

        return [$pendingUniformRequests, $customItems];
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
