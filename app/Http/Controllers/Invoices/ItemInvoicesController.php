<?php

namespace App\Http\Controllers\Invoices;

use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;

class ItemInvoicesController
{
    public function __construct(private InvoiceRepository $invoiceRepository)
    {

    }

    public function index(Request $request)
    {
        if($request->ajax()) {
            return datatables()->of($this->invoiceRepository->getAllItems())
            ->filterColumn('is_paid', fn ($query, $keyword) => $query->where('is_paid', $keyword))
            ->filterColumn('created_at', fn ($query, $keyword) => $query->whereDate('created_at', $keyword))

            ->toJson();
        }

        return view('invoices.index-detail');
    }
}
