<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\PaymentInvoiceRequest;
use App\Http\Resources\API\Notification\Invoices\InvoiceCollection;
use App\Http\Resources\API\Notification\Invoices\InvoiceResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceStatistcsResource;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRequestRepository;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceRepository $invoice_repository, private PaymentRequestRepository $payment_request_repository)
    {
        //
    }

    public function index(): InvoiceCollection
    {
        return new InvoiceCollection($this->invoice_repository->invoicesPlayer());
    }

    public function statistics(): InvoiceStatistcsResource|JsonResponse
    {
        return new InvoiceStatistcsResource($this->invoice_repository->statisticsPlayer());
    }

    public function payment(PaymentInvoiceRequest $request): InvoiceResource|JsonResponse
    {
        $invoice = $this->payment_request_repository->createPaymentRequest($request->validated());

        if (!$invoice) {
            return response()->json([
                'message' => 'No fue posible registrar el comprobante de pago.',
            ], 500);
        }

        return new InvoiceResource($invoice);
    }

    public function show(int $invoice): InvoiceResource
    {
        return new InvoiceResource($this->invoice_repository->findPlayerInvoiceOrFail($invoice));
    }
}
