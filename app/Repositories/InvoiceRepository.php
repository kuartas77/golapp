<?php

namespace App\Repositories;

use App\Http\Requests\InvoiceAddPaymentRequest;
use App\Http\Requests\InvoiceStoreRequest;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class InvoiceRepository
{
    public function query(): Builder
    {
        $school_id = getSchool(auth()->user())->id;

        return Invoice::with(['inscription.player', 'trainingGroup'])
            ->where('school_id', $school_id);
    }

    public function createInvoice($inscriptionId)
    {
        $school = getSchool(auth()->user());
        $inscription = Inscription::with(['player', 'trainingGroup'])
            ->where('school_id', $school->id)
            ->findOrFail($inscriptionId);

        // Buscar registro en payments para el año actual
        $currentYear = date('Y');
        $payment = Payment::where('inscription_id', $inscriptionId)
            ->where('year', $currentYear)
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

    public function storeInvoide(InvoiceStoreRequest $request): mixed
    {
        // Generar número de factura único
        $invoiceNumber = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('Ymd');

        // Verificar que no exista
        while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            $invoiceNumber = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('Ymd');
        }

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'inscription_id' => $request->inscription_id,
            'training_group_id' => $request->training_group_id,
            'year' => $request->year,
            'student_name' => $request->student_name,
            'issue_date' => now()->toDateString(),
            'due_date' => $request->due_date,
            'status' => 'pending',
            'school_id' =>  $request->school_id,
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $itemData) {

            $invoice->items()->create([
                'type' => $itemData['type'],
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'month' => $itemData['month'] ?? null,
                'payment_id' => $itemData['payment_id'] ?? null,
                'is_paid' => false,
            ]);
        }

        return $invoice->id;
    }

    public function addPayment(InvoiceAddPaymentRequest $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $paymentReceived = PaymentReceived::query()->create([
            'invoice_id' => $invoiceId,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
            'school_id' => $request->school_id,
            'created_by' => auth()->id(),
        ]);

        // Actualizar totales de la factura
        $invoice->updateTotals();

        // Si el usuario seleccionó ítems específicos para marcar como pagados
        if ($request->has('paid_items')) {
            foreach ($request->paid_items as $itemId) {
                $item = $invoice->items()->find($itemId);
                if ($item) {
                    $item->update(['is_paid' => true, 'payment_received_id' => $paymentReceived->id]);
                }
            }

            // Si hay ítems de meses marcados como pagados, actualizar la tabla payments original
            $invoice->markMonthsAsPaid();
        }
    }

    public function getAllItems()
    {
        $school_id = getSchool(auth()->user())->id;
        return InvoiceItem::query()
            ->select(['invoice_items.*', 'payments_received.payment_method'])
            ->leftJoin('payments_received', 'payment_received_id', 'payments_received.id')
            ->withWhereHas('invoice', fn($q) => $q->where('school_id', $school_id));
    }
}
