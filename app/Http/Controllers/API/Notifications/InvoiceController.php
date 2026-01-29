<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\PaymentInvoiceRequest;
use App\Http\Resources\API\Notification\Invoices\InvoiceCollection;
use App\Http\Resources\API\Notification\Invoices\InvoiceResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceStatistcsResource;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceRepository $invoice_repository)
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

    public function payment(PaymentInvoiceRequest $request): InvoiceResource
    {
        $invoice = $this->invoice_repository->createPaymentRequest($request->validated());
        return new InvoiceResource($invoice);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice->load('items'));
    }
}
