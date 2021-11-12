<?php

namespace App\Exports;

use App\Repositories\PaymentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentsExport implements FromView, WithTitle
{
    use Exportable;

    private $request;
    private $deleted;

    /**
     * PaymentsExport constructor.
     * @param Request $request
     * @param $deleted
     */
    public function __construct(Request $request, $deleted)
    {
        $this->request = $request;
        $this->deleted = $deleted;
    }

    public function view(): View
    {
        $payments = app(PaymentRepository::class)->filterSelect($this->request, $this->deleted);

        return view('exports.payment_excel', [
            'payments' => $payments->get(),
        ]);
    }

    public function title(): string
    {
        return "Pagos";
    }
}
