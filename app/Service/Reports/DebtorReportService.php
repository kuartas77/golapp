<?php

namespace App\Service\Reports;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\TrainingGroup;
use App\Traits\PDFTrait;
use Illuminate\Support\Collection;

class DebtorReportService
{
    use PDFTrait;

    public function years(int $schoolId): Collection
    {
        $paymentYears = Payment::query()
            ->where('school_id', $schoolId)
            ->distinct()
            ->pluck('year');

        $invoiceYears = Invoice::query()
            ->where('school_id', $schoolId)
            ->distinct()
            ->pluck('year');

        return $paymentYears
            ->merge($invoiceYears)
            ->push(now()->year)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values();
    }

    public function groupOptions(int $schoolId, int $year): Collection
    {
        $paymentGroupIds = Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->pluck('training_group_id');

        $invoiceGroupIds = Invoice::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->pluck('training_group_id');

        return TrainingGroup::query()
            ->whereIn('id', $paymentGroupIds->merge($invoiceGroupIds)->filter()->unique()->values())
            ->schoolId()
            ->orderBy('name')
            ->get()
            ->map(fn ($group) => [
                'id' => $group->id,
                'text' => $group->full_schedule_group,
                'value' => $group->id,
                'label' => $group->full_schedule_group,
            ])
            ->values();
    }

    public function rows(array $filters): Collection
    {
        $schoolId = (int) data_get($filters, 'school_id');
        $year = (int) data_get($filters, 'year', now()->year);
        $trainingGroupId = (int) data_get($filters, 'training_group_id', 0);

        $rows = collect();
        $invoicedMonthlyKeys = $this->invoicedMonthlyKeys($schoolId, $year, $trainingGroupId);

        $this->paymentsQuery($schoolId, $year, $trainingGroupId)
            ->get()
            ->each(function (Payment $payment) use ($rows, $invoicedMonthlyKeys) {
                $monthlyDebt = $this->monthlyDebtForPayment($payment, $invoicedMonthlyKeys);

                if ($monthlyDebt <= 0) {
                    return;
                }

                $row = $this->baseRowFromPayment($payment);
                $row['monthly_debt'] = $monthlyDebt;
                $row['total_debt'] = $monthlyDebt;

                $rows->put($row['inscription_id'], $row);
            });

        $this->invoicesQuery($schoolId, $year, $trainingGroupId)
            ->get()
            ->each(function (Invoice $invoice) use ($rows) {
                $invoiceDebt = (float) $invoice->total_amount - (float) $invoice->paid_amount;

                if ($invoiceDebt <= 0) {
                    return;
                }

                $key = $invoice->inscription_id;
                $row = $rows->get($key, $this->baseRowFromInvoice($invoice));

                $row['invoice_debt'] += $invoiceDebt;
                $row['total_debt'] = $row['monthly_debt'] + $row['invoice_debt'];

                $rows->put($key, $row);
            });

        return $rows
            ->filter(fn ($row) => $row['total_debt'] > 0)
            ->sortBy([
                ['training_group', 'asc'],
                ['student_name', 'asc'],
            ])
            ->values();
    }

    public function exportPdf(array $filters, bool $stream = true)
    {
        $school = getSchool(auth()->user());
        $rows = $this->rows($filters + ['school_id' => $school->id]);
        $date = now()->format('d-m-Y H:i:s');

        $data = [
            'school' => $school,
            'rows' => $rows,
            'date' => $date,
            'year' => (int) data_get($filters, 'year', now()->year),
            'group' => $this->selectedGroupLabel($filters),
        ];

        $filename = "Deudores {$date}.pdf";
        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'debtors.blade.php');

        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    private function paymentsQuery(int $schoolId, int $year, int $trainingGroupId)
    {
        return Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId))
            ->with([
                'inscription' => fn ($query) => $query->with(['player', 'trainingGroup'])->withTrashed(),
                'training_group',
            ])
            ->withTrashed();
    }

    private function invoicesQuery(int $schoolId, int $year, int $trainingGroupId)
    {
        return Invoice::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereIn('status', ['pending', 'partial'])
            ->whereRaw('total_amount > paid_amount')
            ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId))
            ->with(['inscription.player', 'trainingGroup']);
    }

    private function invoicedMonthlyKeys(int $schoolId, int $year, int $trainingGroupId): Collection
    {
        return InvoiceItem::query()
            ->where('is_paid', false)
            ->whereNotNull('payment_id')
            ->whereNotNull('month')
            ->whereHas('invoice', function ($query) use ($schoolId, $year, $trainingGroupId) {
                $query->where('school_id', $schoolId)
                    ->where('year', $year)
                    ->whereIn('status', ['pending', 'partial'])
                    ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId));
            })
            ->get(['payment_id', 'month'])
            ->mapWithKeys(fn ($item) => [$this->monthlyKey((int) $item->payment_id, (string) $item->month) => true]);
    }

    private function monthlyDebtForPayment(Payment $payment, Collection $invoicedMonthlyKeys): float
    {
        return collect(Payment::paymentFields())->sum(function (string $field) use ($payment, $invoicedMonthlyKeys) {
            if ((int) $payment->{$field} !== Payment::$debt) {
                return 0;
            }

            if ($invoicedMonthlyKeys->has($this->monthlyKey((int) $payment->id, $field))) {
                return 0;
            }

            $amountField = Payment::amountFieldFor($field);

            return (float) ($amountField ? $payment->{$amountField} : 0);
        });
    }

    private function monthlyKey(int $paymentId, string $month): string
    {
        return "{$paymentId}:{$month}";
    }

    private function baseRowFromPayment(Payment $payment): array
    {
        $inscription = $payment->inscription;
        $player = $inscription?->player;
        $group = $inscription?->trainingGroup ?? $payment->training_group;

        return $this->baseRow(
            (int) $payment->inscription_id,
            $player?->id,
            $payment->unique_code,
            $player?->full_names ?? $inscription?->player?->full_names ?? $payment->unique_code,
            $inscription?->category ?? $payment->category,
            $group?->full_group ?? $group?->name
        );
    }

    private function baseRowFromInvoice(Invoice $invoice): array
    {
        $inscription = $invoice->inscription;
        $player = $inscription?->player;
        $group = $invoice->trainingGroup;

        return $this->baseRow(
            (int) $invoice->inscription_id,
            $player?->id,
            $player?->unique_code ?? $inscription?->unique_code ?? '',
            $player?->full_names ?? $invoice->student_name,
            $inscription?->category,
            $group?->full_group ?? $group?->name
        );
    }

    private function baseRow(int $inscriptionId, ?int $playerId, string $uniqueCode, string $studentName, ?string $category, ?string $trainingGroup): array
    {
        return [
            'player_id' => $playerId,
            'inscription_id' => $inscriptionId,
            'unique_code' => $uniqueCode,
            'student_name' => $studentName,
            'category' => $category ?? '',
            'training_group' => $trainingGroup ?? '',
            'monthly_debt' => 0.0,
            'invoice_debt' => 0.0,
            'total_debt' => 0.0,
        ];
    }

    private function selectedGroupLabel(array $filters): string
    {
        $trainingGroupId = (int) data_get($filters, 'training_group_id', 0);

        if ($trainingGroupId === 0) {
            return 'Todos los grupos';
        }

        $group = TrainingGroup::query()->find($trainingGroupId);

        return $group?->full_group ?? 'Grupo seleccionado';
    }
}
