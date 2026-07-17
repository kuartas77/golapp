<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Models\Payment;
use App\Models\School;
use App\Traits\PDFTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mpdf\Output\Destination;

class MonthlyPaymentReceiptService
{
    use PDFTrait;

    private const MONTH_FIELDS = [
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
    ];

    public function receiptRows(Request $request): array
    {
        $school = getSchool($request->user());
        $rows = $this->receiptRowsQuery($request, (int) $school->id)
            ->orderBy('unique_code')
            ->orderBy('month_order')
            ->get()
            ->map(fn ($row) => $this->formatRow($row));

        return [
            'rows' => $rows->values(),
            'count' => $rows->count(),
        ];
    }

    public function datatableRows(Request $request): JsonResponse
    {
        $school = getSchool($request->user());
        $query = $this->receiptRowsQuery($request, (int) $school->id);

        return datatables()->of($query)
            ->addColumn('status_label', fn ($row) => $this->statusLabel((int) $row->status))
            ->addColumn('pdf_url', fn ($row) => $this->receiptUrl((int) $row->payment_id, (string) $row->month))
            ->filterColumn('player_name', function ($query, $keyword) {
                $query->where(function ($playerQuery) use ($keyword) {
                    $playerQuery->where('r.player_name', 'like', "%{$keyword}%")
                        ->orWhere('r.unique_code', 'like', "%{$keyword}%")
                        ->orWhere('r.training_group', 'like', "%{$keyword}%")
                        ->orWhere('r.category', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('month_label', fn ($query, $keyword) => $query->where('r.month_label', 'like', "%{$keyword}%"))
            ->filterColumn('status_label', function ($query, $keyword) {
                $matchingStatuses = collect(self::paidStatuses())
                    ->filter(fn ($status) => Str::contains(
                        Str::lower($this->statusLabel((int) $status)),
                        Str::lower((string) $keyword)
                    ))
                    ->values()
                    ->all();

                $query->whereIn('r.status', $matchingStatuses ?: [-1]);
            })
            ->toJson();
    }

    public function streamReceipt(Payment $payment, string $month)
    {
        $payment = $this->resolveReceiptablePayment($payment, $month);
        $filename = $this->prepareMonthlyReceiptPdf($payment, $month, getSchool(auth()->user()));

        return $this->stream($filename);
    }

    public function receiptPdfAttachment(Payment $payment, string $month, ?School $school = null): array
    {
        $filename = $this->prepareMonthlyReceiptPdf($payment, $month, $school);

        return [
            'filename' => $filename,
            'content' => $this->getMpdf()->Output(null, Destination::STRING_RETURN),
            'mime' => 'application/pdf',
        ];
    }

    public function resolveReceiptablePayment(Payment $payment, string $month): Payment
    {
        abort_unless($this->isMonthlyField($month), 404);

        $payment = Payment::query()
            ->with(['inscription.player', 'training_group'])
            ->whereKey($payment->id)
            ->where('school_id', getSchool(auth()->user())->id)
            ->whereHas('inscription.player')
            ->firstOrFail();

        abort_unless($this->isPaidStatus((int) $payment->{$month}), 404);

        return $payment;
    }

    private function receiptRowsQuery(Request $request, int $schoolId)
    {
        $year = (int) $request->input('year', now()->year);
        $category = $request->input('category');
        $uniqueCode = $request->input('unique_code');
        $playerName = trim((string) $request->input('player_name', ''));
        $trainingGroupId = (int) $request->input('training_group_id', 0);
        $union = null;

        foreach (config('variables.KEY_INDEX_MONTHS', []) as $monthOrder => $field) {
            $amountField = Payment::amountFieldFor($field);
            $monthQuery = DB::table('payments as p')
                ->join('inscriptions as i', 'i.id', '=', 'p.inscription_id')
                ->join('players as pl', 'pl.id', '=', 'i.player_id')
                ->leftJoin('training_groups as tg', 'tg.id', '=', 'p.training_group_id')
                ->where('p.school_id', $schoolId)
                ->where('p.year', $year)
                ->whereNull('p.deleted_at')
                ->where('i.year', $year)
                ->whereIn("p.{$field}", self::paidStatuses())
                ->when($uniqueCode, fn ($query) => $query->where('p.unique_code', $uniqueCode))
                ->when($playerName !== '', fn ($query) => $query->where(function ($playerQuery) use ($playerName) {
                    $playerQuery
                        ->where('pl.names', 'like', "%{$playerName}%")
                        ->orWhere('pl.last_names', 'like', "%{$playerName}%")
                        ->orWhereRaw("CONCAT_WS(' ', pl.names, pl.last_names) LIKE ?", ["%{$playerName}%"]);
                }))
                ->when($trainingGroupId !== 0, fn ($query) => $query->where('p.training_group_id', $trainingGroupId))
                ->when($category, fn ($query) => $query->where('i.category', $category))
                ->selectRaw(
                    "p.id as payment_id,
                    p.unique_code,
                    TRIM(CONCAT_WS(' ', pl.names, pl.last_names)) as player_name,
                    i.category,
                    tg.name as training_group,
                    p.year,
                    ? as month_order,
                    ? as month,
                    ? as month_label,
                    p.{$amountField} as amount,
                    p.{$field} as status",
                    [(int) $monthOrder, $field, $this->monthLabel($field)]
                );

            $union = $union ? $union->unionAll($monthQuery) : $monthQuery;
        }

        return DB::query()
            ->fromSub($union, 'r')
            ->select('r.*');
    }

    private function formatRow(object $row): array
    {
        $status = (int) $row->status;

        return [
            'payment_id' => (int) $row->payment_id,
            'unique_code' => $row->unique_code,
            'player_name' => $row->player_name,
            'category' => $row->category,
            'training_group' => $row->training_group,
            'year' => (int) $row->year,
            'month' => $row->month,
            'month_label' => $row->month_label,
            'amount' => (int) $row->amount,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'pdf_url' => $this->receiptUrl((int) $row->payment_id, (string) $row->month),
        ];
    }

    private function receiptUrl(int $paymentId, string $month): string
    {
        return route('payments.monthly-receipts.show', [
            'payment' => $paymentId,
            'month' => $month,
        ]);
    }

    private function prepareMonthlyReceiptPdf(Payment $payment, string $month, ?School $school = null): string
    {
        abort_unless($this->isMonthlyField($month), 404);

        $payment->loadMissing(['inscription.player', 'training_group', 'school']);
        $school ??= $payment->school;
        $amountField = Payment::amountFieldFor($month);

        $data = [
            'school' => $school,
            'payment' => $payment,
            'player' => $payment->inscription->player,
            'month_field' => $month,
            'month_label' => $this->monthLabel($month),
            'amount' => (int) $payment->{$amountField},
            'status_label' => $this->statusLabel((int) $payment->{$month}),
            'issued_at' => now(),
        ];

        $filename = sprintf(
            'Recibo mensualidad %s %s %s.pdf',
            Str::slug((string) $payment->unique_code),
            $month,
            $payment->year
        );

        $this->setConfigurationMpdf([
            'format' => 'A6',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 6,
            'margin_bottom' => 6,
        ]);
        $this->createPDF($data, 'payments/monthly-receipt.blade.php', false, true);

        return $filename;
    }

    private function isMonthlyField(string $field): bool
    {
        return in_array($field, self::MONTH_FIELDS, true);
    }

    private function monthLabel(string $field): string
    {
        return config("variables.KEY_INDEX_MONTHS_LABEL.{$field}", ucfirst($field));
    }

    private function statusLabel(int $status): string
    {
        return config("variables.KEY_PAYMENTS_SELECT.{$status}", (string) $status);
    }

    private function isPaidStatus(int $status): bool
    {
        return in_array($status, self::paidStatuses(), true);
    }

    public static function paidStatuses(): array
    {
        return PaymentStatusCatalog::paidStatuses();
    }
}
