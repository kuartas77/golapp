<?php

namespace App\Service\Reports;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InscriptionCustomCharge;
use App\Models\Payment;
use App\Models\TrainingGroup;
use App\Traits\PDFTrait;
use Illuminate\Support\Carbon;
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

        $customChargeYears = InscriptionCustomCharge::query()
            ->where('school_id', $schoolId)
            ->where('status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('invoice_item_id')
            ->whereNotNull('due_date')
            ->pluck('due_date')
            ->map(fn ($date) => Carbon::parse($date)->year);

        return $paymentYears
            ->merge($invoiceYears)
            ->merge($customChargeYears)
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

        $customChargeGroupIds = InscriptionCustomCharge::query()
            ->where('inscription_custom_charges.school_id', $schoolId)
            ->where('inscription_custom_charges.status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('inscription_custom_charges.invoice_item_id')
            ->whereYear('inscription_custom_charges.due_date', $year)
            ->join('inscriptions', 'inscription_custom_charges.inscription_id', '=', 'inscriptions.id')
            ->pluck('inscriptions.training_group_id');

        return TrainingGroup::query()
            ->whereIn('id', $paymentGroupIds->merge($invoiceGroupIds)->merge($customChargeGroupIds)->filter()->unique()->values())
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

                if ($monthlyDebt['amount'] <= 0) {
                    return;
                }

                $row = $this->baseRowFromPayment($payment);
                $row = $rows->get($row['inscription_id'], $row);
                $row = $this->appendDebt($row, 'Mensualidades: Debe '.$monthlyDebt['label'], $monthlyDebt['amount']);

                $rows->put($row['inscription_id'], $row);
            });

        $this->invoiceItemsQuery($schoolId, $year, $trainingGroupId)
            ->get()
            ->each(function (InvoiceItem $item) use ($rows) {
                $itemDebt = (float) $item->total;

                if ($itemDebt <= 0) {
                    return;
                }

                $row = $this->baseRowFromInvoiceItem($item);
                $row = $rows->get($row['inscription_id'], $row);
                $row = $this->appendDebt($row, $this->itemLabel($item), $itemDebt);

                $rows->put($row['inscription_id'], $row);
            });

        $this->customChargesQuery($schoolId, $year, $trainingGroupId)
            ->get()
            ->each(function (InscriptionCustomCharge $charge) use ($rows) {
                $chargeDebt = (float) $charge->value;

                if ($chargeDebt <= 0) {
                    return;
                }

                $row = $this->baseRowFromCustomCharge($charge);
                $row = $rows->get($row['inscription_id'], $row);
                $row = $this->appendDebt($row, $this->customChargeLabel($charge), $chargeDebt);

                $rows->put($row['inscription_id'], $row);
            });

        return $rows
            ->filter(fn ($row) => $row['total_debt'] > 0)
            ->sortBy([
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
            'showTotalDebt' => filter_var(data_get($filters, 'show_total_debt', false), FILTER_VALIDATE_BOOLEAN),
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

    private function invoiceItemsQuery(int $schoolId, int $year, int $trainingGroupId)
    {
        return InvoiceItem::query()
            ->where('is_paid', false)
            ->whereHas('invoice', function ($query) use ($schoolId, $year, $trainingGroupId) {
                $query->where('school_id', $schoolId)
                    ->where('year', $year)
                    ->whereIn('status', ['pending', 'partial'])
                    ->whereRaw('total_amount > paid_amount')
                    ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId));
            })
            ->with(['invoice.inscription.player', 'invoice.trainingGroup']);
    }

    private function customChargesQuery(int $schoolId, int $year, int $trainingGroupId)
    {
        return InscriptionCustomCharge::query()
            ->where('school_id', $schoolId)
            ->where('status', InscriptionCustomCharge::STATUS_DUE)
            ->whereNull('invoice_item_id')
            ->whereYear('due_date', $year)
            ->whereHas('inscription', function ($query) use ($trainingGroupId) {
                $query->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId));
            })
            ->with(['inscription.player', 'inscription.trainingGroup', 'invoiceCustomItem']);
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

    private function monthlyDebtForPayment(Payment $payment, Collection $invoicedMonthlyKeys): array
    {
        $months = collect(Payment::paymentFields())->map(function (string $field) use ($payment, $invoicedMonthlyKeys) {
            if ((int) $payment->{$field} !== Payment::$debt) {
                return null;
            }

            if ($invoicedMonthlyKeys->has($this->monthlyKey((int) $payment->id, $field))) {
                return null;
            }

            $amountField = Payment::amountFieldFor($field);

            return [
                'label' => $this->monthLabel($field),
                'amount' => (float) ($amountField ? $payment->{$amountField} : 0),
            ];
        })->filter()->values();

        return [
            'amount' => (float) $months->sum('amount'),
            'label' => $months->pluck('label')->implode(', '),
        ];
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

    private function baseRowFromInvoiceItem(InvoiceItem $item): array
    {
        $invoice = $item->invoice;
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

    private function baseRowFromCustomCharge(InscriptionCustomCharge $charge): array
    {
        $inscription = $charge->inscription;
        $player = $inscription?->player;
        $group = $inscription?->trainingGroup;

        return $this->baseRow(
            (int) $charge->inscription_id,
            $player?->id,
            $player?->unique_code ?? $inscription?->unique_code ?? '',
            $player?->full_names ?? $charge->name,
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
            'debt_items' => [],
            'debt_label' => '',
            'total_debt' => 0.0,
        ];
    }

    private function appendDebt(array $row, string $label, float $amount): array
    {
        $row['debt_items'][] = [
            'label' => $label,
            'amount' => $amount,
        ];
        $row['debt_label'] = collect($row['debt_items'])->pluck('label')->implode("\n");
        $row['total_debt'] += $amount;

        return $row;
    }

    private function monthLabel(string $field): string
    {
        if ($field === 'enrollment') {
            return 'Matrícula';
        }

        return config("variables.KEY_INDEX_MONTHS_LABEL.{$field}", ucfirst($field));
    }

    private function itemLabel(InvoiceItem $item): string
    {
        $type = match ($item->type) {
            'monthly' => 'Mensualidad',
            'enrollment' => 'Matrícula',
            default => 'Item',
        };

        $invoiceNumber = $item->invoice?->invoice_number;
        $description = trim((string) $item->description);

        return trim("{$invoiceNumber} - {$type}: {$description}", ' -:');
    }

    private function customChargeLabel(InscriptionCustomCharge $charge): string
    {
        return trim((string) ($charge->name ?: $charge->invoiceCustomItem?->name));
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
