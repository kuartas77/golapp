<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\PaymentRequest;
use App\Models\Player;
use App\Traits\UploadFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PaymentRequestRepository
{
    use UploadFile;

    public function createPaymentRequest(array $validated): ?Invoice
    {
        $invoice = null;
        try {
            DB::beginTransaction();

            /** @var Player $player */
            $player = request()->user();
            $player->loadMissing(['schoolData', 'inscription']);
            $school = $player->schoolData;
            $invoice = $player->inscription?->invoices()
                ->whereKey($validated['invoice_id'])
                ->whereIn('status', ['pending', 'partial'])
                ->first();

            if (!$invoice) {
                throw new ModelNotFoundException();
            }

            $paymentRequest = new PaymentRequest();
            $paymentRequest->school_id = $player->school_id;
            $paymentRequest->player_id = $player->id;
            $paymentRequest->invoice_id = $invoice->id;
            $paymentRequest->amount = $validated['amount'];
            $paymentRequest->description = $validated['description'];
            $paymentRequest->reference_number = $validated['reference_number'];
            $paymentRequest->payment_method = $validated['payment_method'];

            $paymentRequest->image = $this->uploadFile($validated['image'], $school->slug, 'invoice_receipts');

            $paymentRequest->save();
            DB::commit();

            $paymentRequest->load('invoice.items');

            $invoice = $paymentRequest->invoice;

        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
        }

        return $invoice;
    }

    public function findForCurrentSchoolOrFail(int $paymentRequestId): PaymentRequest
    {
        return PaymentRequest::query()->schoolId()->findOrFail($paymentRequestId);
    }

    public function getPaymentRequestsQuery()
    {
        $generalQuery = PaymentRequest::query()->with(['invoice:id,invoice_number,total_amount', 'player:id,unique_code,names,last_names,photo'])
            ->select(['payment_request.*', 'invoices.invoice_number', 'training_groups.name'])
            ->join('invoices', 'payment_request.invoice_id', '=', 'invoices.id')
            ->join('training_groups', 'invoices.training_group_id', '=', 'training_groups.id')
            ->join('players', 'payment_request.player_id', '=', 'players.id')
            ->whereHas('invoice', fn($q) => $q->whereIn('status', ['partial','pending']))
            ->schoolId();

        return datatables()->of($generalQuery)
        ->filterColumn('created_at', fn($query, $keyword) => $query->whereDate('payment_request.created_at', $keyword))
        ->toJson();
    }
}
