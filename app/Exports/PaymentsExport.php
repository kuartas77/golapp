<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Repositories\PaymentRepository;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class PaymentsExport implements ShouldQueue, FromView, WithTitle, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    use Exportable;

    public function __construct(private array $params, private $deleted)
    {
        //
    }

    public function view(): View
    {
        $queryPayments = app(PaymentRepository::class)->filterSelect($this->params, $this->deleted);

        $accumulate = $this->accumulateResult();

        return view('exports.payment_excel', [
            'payments' => $queryPayments->get(),
            'accumulate' => $accumulate
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastCell = ($event->sheet->getHighestRow()-3);
                $lastCellSum = ($event->sheet->getHighestRow()-4);

                $event->sheet->setCellValue('C'. ($lastCell), 'Totales:');
                $event->sheet->setCellValue('D'. ($lastCell), '=SUM(D2:D'.($lastCellSum).')');
                $event->sheet->setCellValue('E'. ($lastCell), '=SUM(E2:E'.($lastCellSum).')');
                $event->sheet->setCellValue('F'. ($lastCell), '=SUM(F2:F'.($lastCellSum).')');
                $event->sheet->setCellValue('G'. ($lastCell), '=SUM(G2:G'.($lastCellSum).')');
                $event->sheet->setCellValue('H'. ($lastCell), '=SUM(H2:H'.($lastCellSum).')');
                $event->sheet->setCellValue('I'. ($lastCell), '=SUM(I2:I'.($lastCellSum).')');
                $event->sheet->setCellValue('J'. ($lastCell), '=SUM(J2:J'.($lastCellSum).')');
                $event->sheet->setCellValue('K'. ($lastCell), '=SUM(K2:K'.($lastCellSum).')');
                $event->sheet->setCellValue('L'. ($lastCell), '=SUM(L2:L'.($lastCellSum).')');
                $event->sheet->setCellValue('M'. ($lastCell), '=SUM(M2:M'.($lastCellSum).')');
                $event->sheet->setCellValue('N'. ($lastCell), '=SUM(N2:N'.($lastCellSum).')');
                $event->sheet->setCellValue('O'. ($lastCell), '=SUM(O2:O'.($lastCellSum).')');
                $event->sheet->setCellValue('P'. ($lastCell), '=SUM(P2:P'.($lastCellSum).')');
            }
        ];
    }

    private function accumulateResult(): array
    {
        $months = array_keys(config('variables.KEY_INDEX_MONTHS_LABEL'));
        $months[] = 'enrollment';
        $sums = [
            'pago' => 0,
            'abono' => 0,
            'pago_efectivo' => 0,
            'pago_consignacion' => 0,
            'pago_anual_consignacion' => 0,
            'pago_anual_efectivo' => 0,
            'acuerdo' => 0,
            'debe' => 0,
            'temporal' => 0,
            'incapacidad' => 0,
            'becado' => 0,
            'definitivo' => 0,
        ];

        $queryWhereAccumulates = app(PaymentRepository::class)->filterSelectRaw($this->params, $this->deleted);

        foreach ($months as $key) {
            $result = $queryWhereAccumulates->select([
                DB::raw("SUM(case when {$key} = 1 then {$key}_amount else 0 end) as pago"),
                DB::raw("SUM(case when {$key} = 3 then {$key}_amount else 0 end) as abono"),
                DB::raw("SUM(case when {$key} = 9 then {$key}_amount else 0 end) as pago_efectivo"),
                DB::raw("SUM(case when {$key} = 10 then {$key}_amount else 0 end) as pago_consignacion"),
                DB::raw("SUM(case when {$key} = 11 then {$key}_amount else 0 end) as pago_anual_consignacion"),
                DB::raw("SUM(case when {$key} = 12 then {$key}_amount else 0 end) as pago_anual_efectivo"),
                DB::raw("SUM(case when {$key} = 13 then {$key}_amount else 0 end) as acuerdo"),
                DB::raw("SUM(case when {$key} = 2 then {$key}_amount else 0 end) as debe"),
                DB::raw("SUM(case when {$key} = 5 then {$key}_amount else 0 end) as temporal"),
                DB::raw("SUM(case when {$key} = 4 then {$key}_amount else 0 end) as incapacidad"),
                DB::raw("SUM(case when {$key} = 8 then {$key}_amount else 0 end) as becado"),
                DB::raw("SUM(case when {$key} = 6 then {$key}_amount else 0 end) as definitivo"),
            ])->first();

            $sums['pago'] += $result->pago;
            $sums['abono'] += $result->abono;
            $sums['pago_efectivo'] += $result->pago_efectivo;
            $sums['pago_consignacion'] += $result->pago_consignacion;
            $sums['pago_anual_consignacion'] += $result->pago_anual_consignacion;
            $sums['pago_anual_efectivo'] += $result->pago_anual_efectivo;
            $sums['acuerdo'] += $result->acuerdo;
            $sums['debe'] += $result->debe;
            $sums['temporal'] += $result->temporal;
            $sums['incapacidad'] += $result->incapacidad;
            $sums['becado'] += $result->becado;
            $sums['definitivo'] += $result->definitivo;
        }
        return $sums;
    }
}
