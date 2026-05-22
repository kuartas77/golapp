<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;

class ItemInvoicesController extends Controller
{
    public function __construct(private InvoiceRepository $invoiceRepository) {}

    public function index(Request $request)
    {
        return datatables()->eloquent($this->invoiceRepository->getAllItems())
            ->filterColumn('is_paid', fn ($query, $keyword) => $query->where('invoice_items.is_paid', $keyword))
            ->filterColumn('created_at', fn ($query, $keyword) => $query->whereDate('invoice_items.created_at', $keyword))
            ->filterColumn('payment_method', fn($query, $keyword) => $query->where('payment_method', $keyword))
            ->orderColumn('invoice_items.is_paid', 'invoice_items.is_paid $1')
            ->toJson();
    }

    public function exportPending()
    {
        return $this->invoiceRepository->exportPendingItems();
    }
}
