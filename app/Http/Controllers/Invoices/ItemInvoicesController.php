<?php

namespace App\Http\Controllers\Invoices;

use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;

class ItemInvoicesController
{
    public function __construct(private InvoiceRepository $invoice_repository)
    {

    }

    public function index(Request $request)
    {
        if($request->ajax()) {
            return datatables()->of($this->invoice_repository->getAllItems())
            ->filterColumn('is_paid', fn ($query, $keyword) => $query->where('is_paid', $keyword))
            ->filterColumn('created_at', fn ($query, $keyword) => $query->whereDate('created_at', $keyword))

            ->toJson();
        }

        return view('invoices.index-detail');
    }
}
