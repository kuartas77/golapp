<?php

declare(strict_types=1);

namespace App\Service\Reports;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Traits\PDFTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

final class ReceivedPaymentReportService
{
    use PDFTrait;

    private const APPLIED_PAYMENT_STATUSES = [1, 3, 9, 10, 11, 12, 15];

    public function years(int $schoolId): Collection
    {
        $paymentYears = Payment::query()
            ->where('school_id', $schoolId)
            ->where(fn ($query) => $this->whereHasAppliedPaymentField($query))
            ->distinct()
            ->pluck('year');

        $invoiceYears = Invoice::query()
            ->where('school_id', $schoolId)
            ->whereHas('inscription.player')
            ->whereHas('items', fn ($query) => $query->where('is_paid', true)->where('total', '>', 0))
            ->distinct()
            ->pluck('year');

        $customChargeYears = InscriptionCustomCharge::query()
            ->where('school_id', $schoolId)
            ->where('status', InscriptionCustomCharge::STATUS_PAID)
            ->whereNull('invoice_item_id')
            ->where('value', '>', 0)
            ->whereNotNull('due_date')
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at')->whereHas('player'))
            ->pluck('due_date')
            ->map(fn ($date) => (int) $date->format('Y'));

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
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at')->whereHas('player'))
            ->where(fn ($query) => $this->whereHasAppliedPaymentField($query))
            ->pluck('training_group_id');

        $invoiceGroupIds = Invoice::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->whereHas('inscription.player')
            ->whereHas('items', fn ($query) => $query->where('is_paid', true)->where('total', '>', 0))
            ->pluck('training_group_id');

        $customChargeGroupIds = InscriptionCustomCharge::query()
            ->where('inscription_custom_charges.school_id', $schoolId)
            ->where('inscription_custom_charges.status', InscriptionCustomCharge::STATUS_PAID)
            ->whereNull('inscription_custom_charges.invoice_item_id')
            ->where('inscription_custom_charges.value', '>', 0)
            ->whereYear('inscription_custom_charges.due_date', $year)
            ->join('inscriptions', 'inscription_custom_charges.inscription_id', '=', 'inscriptions.id')
            ->whereNull('inscriptions.deleted_at')
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('players')
                ->whereColumn('players.id', 'inscriptions.player_id')
                ->whereNull('players.deleted_at'))
            ->pluck('inscriptions.training_group_id');

        return TrainingGroup::query()
            ->whereIn('id', $paymentGroupIds->merge($invoiceGroupIds)->merge($customChargeGroupIds)->filter()->unique())
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get()
            ->map(fn (TrainingGroup $group) => [
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
        $playerSearch = trim((string) data_get($filters, 'player_search', ''));
        $rows = collect();

        $invoiceItems = $this->paidInvoiceItems($schoolId, $year, $trainingGroupId, $playerSearch)->get();
        $invoicedPaymentKeys = $invoiceItems
            ->filter(fn (InvoiceItem $item) => $item->payment_id && $item->month)
            ->mapWithKeys(fn (InvoiceItem $item) => [
                $this->paymentFieldKey((int) $item->payment_id, (string) $item->month) => true,
            ]);

        $this->payments($schoolId, $year, $trainingGroupId, $playerSearch)
            ->get()
            ->each(function (Payment $payment) use ($rows, $invoicedPaymentKeys): void {
                $row = $this->baseRowFromPayment($payment);

                foreach (Payment::paymentFields() as $field) {
                    $status = (int) $payment->{$field};
                    $amountField = Payment::amountFieldFor($field);
                    $amount = (float) ($amountField ? $payment->{$amountField} : 0);

                    if (! in_array($status, self::APPLIED_PAYMENT_STATUSES, true)
                        || $amount <= 0
                        || $invoicedPaymentKeys->has($this->paymentFieldKey((int) $payment->id, $field))) {
                        continue;
                    }

                    $label = $this->monthLabel($field);

                    if ($status === Payment::$paid_) {
                        $label .= ' (Abonó)';
                    }

                    $row = $this->appendPayment($row, $label, $amount);
                }

                if ($row['payment_items'] !== []) {
                    $rows->put($row['inscription_id'], $row);
                }
            });

        $invoiceItems->each(function (InvoiceItem $item) use ($rows): void {
            $row = $this->baseRowFromInvoiceItem($item);
            $row = $rows->get($row['inscription_id'], $row);
            $row = $this->appendPayment($row, $this->invoiceItemLabel($item), (float) $item->total);
            $rows->put($row['inscription_id'], $row);
        });

        $this->directPaidCustomCharges($schoolId, $year, $trainingGroupId, $playerSearch)
            ->get()
            ->each(function (InscriptionCustomCharge $charge) use ($rows): void {
                $row = $this->baseRowFromCustomCharge($charge);
                $row = $rows->get($row['inscription_id'], $row);
                $row = $this->appendPayment($row, trim((string) $charge->name), (float) $charge->value);
                $rows->put($row['inscription_id'], $row);
            });

        return $rows
            ->filter(fn (array $row) => $row['total_paid'] > 0)
            ->sort(function (array $left, array $right): int {
                $groupComparison = (int) ($left['training_group_id'] ?? 0) <=> (int) ($right['training_group_id'] ?? 0);

                if ($groupComparison !== 0) {
                    return $groupComparison;
                }

                $categoryComparison = strnatcasecmp($left['category'], $right['category']);

                return $categoryComparison !== 0
                    ? $categoryComparison
                    : strcasecmp($left['student_name'], $right['student_name']);
            })
            ->values();
    }

    public function exportPdf(array $filters, bool $stream = true)
    {
        $school = getSchool(auth()->user());
        $filename = $this->renderPdf($filters + ['school_id' => $school->id], $school);

        return $stream ? $this->stream($filename) : $this->output($filename);
    }

    public function pdfAttachment(array $filters, School $school): array
    {
        $filename = $this->renderPdf($filters + ['school_id' => $school->id], $school);
        /** @var Mpdf $mpdf */
        $mpdf = $this->getMpdf();

        return [
            'content' => $mpdf->Output(null, Destination::STRING_RETURN),
            'filename' => $filename,
            'mime' => 'application/pdf',
        ];
    }

    private function payments(int $schoolId, int $year, int $trainingGroupId, string $playerSearch)
    {
        $query = Payment::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId))
            ->whereHas('inscription', fn ($query) => $query->whereNull('inscriptions.deleted_at')->whereHas('player'))
            ->with(['inscription.player', 'inscription.trainingGroup', 'training_group']);

        $this->applyPlayerSearch($query, 'inscription.player', $playerSearch);

        return $query;
    }

    private function whereHasAppliedPaymentField($query): void
    {
        foreach (Payment::paymentFields() as $index => $field) {
            $amountField = Payment::amountFieldFor($field);
            $method = $index === 0 ? 'where' : 'orWhere';

            $query->{$method}(fn ($fieldQuery) => $fieldQuery
                ->whereIn($field, self::APPLIED_PAYMENT_STATUSES)
                ->where($amountField, '>', 0));
        }
    }

    private function paidInvoiceItems(int $schoolId, int $year, int $trainingGroupId, string $playerSearch)
    {
        $query = InvoiceItem::query()
            ->where('is_paid', true)
            ->where('total', '>', 0)
            ->whereHas('invoice', fn ($query) => $query
                ->where('school_id', $schoolId)
                ->where('year', $year)
                ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId))
                ->whereHas('inscription.player'))
            ->with(['invoice.inscription.player', 'invoice.inscription.trainingGroup', 'invoice.trainingGroup']);

        $this->applyPlayerSearch($query, 'invoice.inscription.player', $playerSearch);

        return $query;
    }

    private function directPaidCustomCharges(int $schoolId, int $year, int $trainingGroupId, string $playerSearch)
    {
        $query = InscriptionCustomCharge::query()
            ->where('school_id', $schoolId)
            ->where('status', InscriptionCustomCharge::STATUS_PAID)
            ->whereNull('invoice_item_id')
            ->whereYear('due_date', $year)
            ->where('value', '>', 0)
            ->whereHas('inscription', fn ($query) => $query
                ->whereNull('inscriptions.deleted_at')
                ->whereHas('player')
                ->when($trainingGroupId !== 0, fn ($query) => $query->where('training_group_id', $trainingGroupId)))
            ->with(['inscription.player', 'inscription.trainingGroup']);

        $this->applyPlayerSearch($query, 'inscription.player', $playerSearch);

        return $query;
    }

    private function applyPlayerSearch(Builder $query, string $relation, string $search): void
    {
        $terms = preg_split('/\s+/u', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($terms === []) {
            return;
        }

        $query->whereHas($relation, function (Builder $playerQuery) use ($terms): void {
            foreach ($terms as $term) {
                $playerQuery->where(function (Builder $termQuery) use ($term): void {
                    $like = "%{$term}%";

                    $termQuery->where('names', 'like', $like)
                        ->orWhere('last_names', 'like', $like)
                        ->orWhere('unique_code', 'like', $like);
                });
            }
        });
    }

    private function baseRowFromPayment(Payment $payment): array
    {
        /** @var Inscription $inscription */
        $inscription = $payment->inscription;
        /** @var Player $player */
        $player = $inscription->player;
        /** @var TrainingGroup|null $group */
        $group = $inscription->trainingGroup ?? $payment->training_group;

        return $this->baseRow(
            (int) $payment->inscription_id,
            (string) ($player->unique_code ?: $payment->unique_code),
            (string) ($player->full_names ?: $payment->unique_code),
            (string) ($inscription->category ?? ''),
            $this->groupLabel($group),
            $group?->id ? (int) $group->id : null,
        );
    }

    private function baseRowFromInvoiceItem(InvoiceItem $item): array
    {
        /** @var Invoice $invoice */
        $invoice = $item->invoice;
        /** @var Inscription $inscription */
        $inscription = $invoice->inscription;
        /** @var Player $player */
        $player = $inscription->player;
        /** @var TrainingGroup|null $group */
        $group = $invoice->trainingGroup ?? $inscription->trainingGroup;

        return $this->baseRow(
            (int) $invoice->inscription_id,
            (string) ($player->unique_code ?: $inscription->unique_code),
            (string) ($player->full_names ?: $invoice->student_name),
            (string) ($inscription->category ?? ''),
            $this->groupLabel($group),
            $group?->id ? (int) $group->id : null,
        );
    }

    private function baseRowFromCustomCharge(InscriptionCustomCharge $charge): array
    {
        /** @var Inscription $inscription */
        $inscription = $charge->inscription;
        /** @var Player $player */
        $player = $inscription->player;
        /** @var TrainingGroup|null $group */
        $group = $inscription->trainingGroup;

        return $this->baseRow(
            (int) $charge->inscription_id,
            (string) ($player->unique_code ?: $inscription->unique_code),
            (string) ($player->full_names ?: $charge->name),
            (string) ($inscription->category ?? ''),
            $this->groupLabel($group),
            $group?->id ? (int) $group->id : null,
        );
    }

    private function baseRow(
        int $inscriptionId,
        string $uniqueCode,
        string $studentName,
        string $category,
        string $trainingGroup,
        ?int $trainingGroupId,
    ): array {
        return [
            'inscription_id' => $inscriptionId,
            'unique_code' => $uniqueCode,
            'student_name' => $studentName,
            'category' => $category,
            'training_group' => $trainingGroup,
            'training_group_id' => $trainingGroupId,
            'payment_items' => [],
            'total_paid' => 0.0,
        ];
    }

    private function appendPayment(array $row, string $label, float $amount): array
    {
        if ($amount <= 0) {
            return $row;
        }

        $row['payment_items'][] = [
            'label' => $label,
            'amount' => $amount,
        ];
        $row['total_paid'] += $amount;

        return $row;
    }

    private function paymentFieldKey(int $paymentId, string $field): string
    {
        return "{$paymentId}:{$field}";
    }

    private function monthLabel(string $field): string
    {
        return $field === 'enrollment'
            ? 'Matrícula'
            : config("variables.KEY_INDEX_MONTHS_LABEL.{$field}", ucfirst($field));
    }

    private function invoiceItemLabel(InvoiceItem $item): string
    {
        $description = trim((string) $item->description);

        return $description !== '' ? $description : match ($item->type) {
            'monthly' => $this->monthLabel((string) $item->month),
            'enrollment' => 'Matrícula',
            default => 'Concepto facturado',
        };
    }

    private function selectedGroupLabel(int $schoolId, array $filters): string
    {
        $trainingGroupId = (int) data_get($filters, 'training_group_id', 0);

        if ($trainingGroupId === 0) {
            return 'Todos los grupos';
        }

        $group = TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->find($trainingGroupId);

        return $group ? $group->full_group : 'Grupo seleccionado';
    }

    private function groupLabel(?TrainingGroup $group): string
    {
        return $group ? ($group->full_group ?: $group->name) : '';
    }

    private function renderPdf(array $filters, School $school): string
    {
        $year = (int) data_get($filters, 'year', now()->year);
        $date = now();
        $data = [
            'school' => $school,
            'rows' => $this->rows($filters + ['school_id' => $school->id]),
            'date' => $date->format('d-m-Y h:i:s A'),
            'year' => $year,
            'group' => $this->selectedGroupLabel((int) $school->id, $filters),
            'playerSearch' => trim((string) data_get($filters, 'player_search', '')),
            'showItemAmounts' => filter_var(data_get($filters, 'show_item_amounts', true), FILTER_VALIDATE_BOOLEAN),
            'showTotalPaid' => filter_var(data_get($filters, 'show_total_paid', true), FILTER_VALIDATE_BOOLEAN),
        ];

        $this->setConfigurationMpdf(['format' => 'A4-L']);
        $this->createPDF($data, 'received-payments.blade.php', mark: false);

        return "Pagos {$year} {$date->format('Y-m-d_H-i-s')}.pdf";
    }
}
