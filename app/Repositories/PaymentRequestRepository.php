<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Traits\ErrorTrait;
use App\Traits\UploadFile;
use Illuminate\Support\Facades\DB;

class PaymentRequestRepository
{
    use ErrorTrait;
    use UploadFile;

    public function createPaymentRequest(array $validated): ?Invoice
    {
        $invoice = null;
        try {
            $player = request()->user();
            $player->load('schoolData');
            $school = $player->schoolData;

            $paymentRequest = new PaymentRequest();
            $paymentRequest->school_id = $player->school_id;
            $paymentRequest->player_id = $player->id;
            $paymentRequest->invoice_id = $validated['invoice_id'];
            $paymentRequest->amount = $validated['amount'];
            $paymentRequest->description = $validated['description'];
            $paymentRequest->reference_number = $validated['reference_number'];
            $paymentRequest->payment_method = $validated['payment_method'];

            $paymentRequest->image = $this->uploadFile($validated['image'], $school->slug, 'invoice_receipts');

            $paymentRequest->save();
            DB::commit();

            $paymentRequest->load('invoice.items');

            $invoice = $paymentRequest->invoice;

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError('InvoiceRepository@createPaymentRequest', $th);
        }

        return $invoice;
    }

    public function getPaymentRequestsQuery()
    {
        $generalQuery = PaymentRequest::query()->with(['invoice:id,invoice_number,total_amount', 'player:id,unique_code,names,last_names,photo'])
            ->select(['payment_request.*', 'invoices.invoice_number', 'training_groups.name'])
            ->join('invoices', 'payment_request.invoice_id', '=', 'invoices.id')
            ->join('training_groups', 'invoices.training_group_id', '=', 'training_groups.id')
            ->join('players', 'payment_request.player_id', '=', 'players.id')
            ->schoolId();

        return datatables()->of($generalQuery)
        ->filterColumn('created_at', fn($query, $keyword) => $query->whereDate('payment_request.created_at', $keyword))
        ->toJson();
    }
}
