<?php

namespace App\Exports;

use App\Repositories\PaymentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PaymentsExport implements FromView, WithTitle, WithColumnFormatting, ShouldAutoSize
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

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'E' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'F' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'G' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'H' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'I' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'J' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'L' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'N' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'O' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
        ];
    }
}
