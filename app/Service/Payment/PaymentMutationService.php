<?php

namespace App\Service\Payment;

use App\Models\Payment;
use App\Models\PaymentChangeLog;
use App\Notifications\MonthlyPaymentReceiptNotification;
use App\Repositories\PaymentRepository;
use App\Service\PaymentAmountResolver;
use App\Service\PlayerCredits\PlayerCreditService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class PaymentMutationService
{
    public function __construct(
        private PaymentAmountResolver $paymentAmountResolver,
        private PlayerCreditService $playerCreditService
    ) {
    }

    public function setPay(array $values, Payment $payment, string $source = 'manual'): bool
    {
        $isPay = false;
        try {
            if ($this->paymentBelongsToDeletedInscription($payment)) {
                return false;
            }

            DB::beginTransaction();
            $normalizedValues = $this->normalizePaymentUpdate($payment, $values);
            $previousValues = $this->paymentValuesSnapshot($payment, $normalizedValues);
            $previousStatuses = $this->monthlyStatuses($payment);
            $this->syncPlayerCreditMovementsBeforeSave($payment, $normalizedValues);
            $isPay = $payment->fill($normalizedValues)->save();

            if ($isPay) {
                $this->recordPaymentChangeLogs($payment, $normalizedValues, $previousValues, $source);
                $this->notifyMonthlyReceiptPayments($payment, $normalizedValues, $previousStatuses);
            }

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            if ($throwable instanceof HttpExceptionInterface) {
                throw $throwable;
            }

            report($throwable);
            $isPay = false;
        }

        return $isPay;
    }

    public function bulkUpdate(array $validated): array
    {
        $paymentIds = collect($validated['payment_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $field = (string) $validated['month'];
        $amountField = Payment::amountFieldFor($field);
        $updatedIds = collect();

        if (! $amountField) {
            return [
                'requested_count' => $paymentIds->count(),
                'updated_count' => 0,
                'skipped_count' => $paymentIds->count(),
                'updated_ids' => [],
            ];
        }

        $eligiblePayments = Payment::query()
            ->with(['inscription'])
            ->whereIn('id', $paymentIds)
            ->where('school_id', $validated['school_id'])
            ->where('year', $validated['year'])
            ->whereNull('deleted_at')
            ->whereHas('inscription', fn ($query) => $query->whereNull('deleted_at'))
            ->get();

        foreach ($eligiblePayments as $payment) {
            $preserveAmount = (int) ($validated['amount'] ?? 0) === 0;
            $bulkAmount = $preserveAmount
                ? (int) $payment->{$amountField}
                : (int) $validated['amount'];

            $saved = $this->setPay([
                'column' => $field,
                $field => (int) $validated['status'],
                $amountField => $bulkAmount,
                'preserve_amount' => $preserveAmount,
            ], $payment, 'bulk');

            if ($saved) {
                $updatedIds->push((int) $payment->id);
            }
        }

        return [
            'requested_count' => $paymentIds->count(),
            'updated_count' => $updatedIds->count(),
            'skipped_count' => $paymentIds->count() - $updatedIds->count(),
            'updated_ids' => $updatedIds->values()->all(),
        ];
    }

    public function history(Payment $payment): array
    {
        return $payment->changeLogs()
            ->with('user:id,name')
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (PaymentChangeLog $log) => [
                'id' => $log->id,
                'field' => $log->field,
                'month_label' => config("variables.KEY_INDEX_MONTHS_LABEL.{$log->field}", $log->field),
                'old_status' => $log->old_status,
                'new_status' => $log->new_status,
                'old_status_label' => config("variables.KEY_PAYMENTS_SELECT.{$log->old_status}", (string) $log->old_status),
                'new_status_label' => config("variables.KEY_PAYMENTS_SELECT.{$log->new_status}", (string) $log->new_status),
                'old_amount' => $log->old_amount,
                'new_amount' => $log->new_amount,
                'source' => $log->source,
                'changed_by' => $log->changed_by,
                'changed_by_name' => $log->user?->name,
                'created_at' => optional($log->created_at)->toDateTimeString(),
            ])
            ->values()
            ->all();
    }

    public function paymentBelongsToDeletedInscription(Payment $payment): bool
    {
        $payment->loadMissing('inscription');

        return (bool) $payment->inscription?->trashed();
    }

    private function paymentValuesSnapshot(Payment $payment, array $normalizedValues): array
    {
        $snapshot = [];

        foreach (Payment::paymentFields() as $field) {
            $amountField = Payment::amountFieldFor($field);

            if (! array_key_exists($field, $normalizedValues) && ! array_key_exists($amountField, $normalizedValues)) {
                continue;
            }

            $snapshot[$field] = [
                'status' => (int) $payment->{$field},
                'amount' => (int) $payment->{$amountField},
            ];
        }

        return $snapshot;
    }

    private function recordPaymentChangeLogs(Payment $payment, array $normalizedValues, array $previousValues, string $source): void
    {
        foreach ($previousValues as $field => $previous) {
            $amountField = Payment::amountFieldFor($field);
            $oldStatus = (int) $previous['status'];
            $newStatus = array_key_exists($field, $normalizedValues)
                ? (int) $normalizedValues[$field]
                : (int) $payment->{$field};
            $oldAmount = (int) $previous['amount'];
            $newAmount = array_key_exists($amountField, $normalizedValues)
                ? (int) $normalizedValues[$amountField]
                : (int) $payment->{$amountField};

            if ($oldStatus === $newStatus && $oldAmount === $newAmount) {
                continue;
            }

            PaymentChangeLog::query()->create([
                'school_id' => $payment->school_id,
                'payment_id' => $payment->id,
                'inscription_id' => $payment->inscription_id,
                'changed_by' => auth()->id(),
                'year' => $payment->year,
                'field' => $field,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'source' => $source,
            ]);
        }
    }

    private function normalizePaymentUpdate(Payment $payment, array $values): array
    {
        $column = data_get($values, 'column');

        if (is_string($column) && $column !== '' && Payment::amountFieldFor($column)) {
            return $this->normalizeColumnUpdate($payment, $column, $values);
        }

        return $this->normalizeFullUpdate($values, $payment);
    }

    private function monthlyStatuses(Payment $payment): array
    {
        return collect(Payment::paymentFields())
            ->reject(fn (string $field) => $field === 'enrollment')
            ->mapWithKeys(fn (string $field) => [$field => (int) $payment->{$field}])
            ->all();
    }

    private function notifyMonthlyReceiptPayments(Payment $payment, array $normalizedValues, array $previousStatuses): void
    {
        $payment->loadMissing(['school', 'inscription.player.people']);

        if (! $payment->school?->send_monthly_payment_receipts) {
            return;
        }

        $guardian = $payment->inscription?->player?->people
            ->first(fn ($person) => (int) $person->tutor === 1 && filter_var($person->email, FILTER_VALIDATE_EMAIL));

        if (! $guardian) {
            return;
        }

        foreach ($previousStatuses as $field => $previousStatus) {
            if (! array_key_exists($field, $normalizedValues)) {
                continue;
            }

            $currentStatus = (int) $payment->{$field};

            if ($this->isPaidStatus($previousStatus) || ! $this->isPaidStatus($currentStatus)) {
                continue;
            }
            $guardian->notify(new MonthlyPaymentReceiptNotification($payment, $field, $payment->school));
        }
    }

    private function isPaidStatus(int $status): bool
    {
        return in_array($status, PaymentStatusCatalog::paidStatuses(), true);
    }

    private function normalizeColumnUpdate(Payment $payment, string $column, array $values): array
    {
        $normalizedValues = [];
        $amountField = Payment::amountFieldFor($column);
        $status = $this->normalizeStatusValue($values[$column] ?? $payment->{$column});
        $amount = $this->normalizeAmountValue($values[$amountField] ?? $payment->{$amountField});
        $defaults = $this->paymentDefaults($payment);

        $normalizedValues[$column] = $status;
        $normalizedValues[$amountField] = $amount;

        if ((bool) data_get($values, 'preserve_amount', false)) {
            return $normalizedValues;
        }

        if ($status === Payment::$permanent_retirement) {
            $this->applyStatusToFollowingFields(
                $normalizedValues,
                $column,
                $status,
                0,
                true,
                $payment
            );

            return $normalizedValues;
        }

        if (in_array($status, [Payment::$annuity_payment_deposit, Payment::$annuity_payment_cash], true)) {
            $this->applyStatusToFollowingFields(
                $normalizedValues,
                $column,
                $status,
                $defaults['annuity'],
                false,
                $payment
            );

            return $normalizedValues;
        }

        $normalizedValues[$amountField] = $this->normalizeAmountByStatus(
            $column,
            $status,
            $amount,
            $defaults
        );

        return $normalizedValues;
    }

    private function normalizeFullUpdate(array $values, ?Payment $payment = null): array
    {
        $normalizedValues = [];
        $defaults = $this->paymentDefaults($payment);

        foreach (Payment::FIELD_AMOUNT_MAP as $field => $amountField) {
            $normalizedValues[$field] = $this->normalizeStatusValue(data_get($values, $field));
            $normalizedValues[$amountField] = $this->normalizeAmountValue(data_get($values, $amountField));
        }

        foreach (Payment::paymentFields() as $field) {
            $amountField = Payment::amountFieldFor($field);
            $status = $normalizedValues[$field];

            if ($status === Payment::$permanent_retirement) {
                $this->applyStatusToFollowingFields(
                    $normalizedValues,
                    $field,
                    $status,
                    0,
                    true
                );

                break;
            }

            if (in_array($status, [Payment::$annuity_payment_deposit, Payment::$annuity_payment_cash], true)) {
                $this->applyStatusToFollowingFields(
                    $normalizedValues,
                    $field,
                    $status,
                    $defaults['annuity']
                );

                break;
            }

            $normalizedValues[$amountField] = $this->normalizeAmountByStatus(
                $field,
                $status,
                $normalizedValues[$amountField],
                $defaults
            );
        }

        return $normalizedValues;
    }

    private function normalizeAmountByStatus(string $field, int $status, int $amount, array $defaults): int
    {
        if (in_array($status, [Payment::$paid, Payment::$paid_cash, Payment::$paid_deposit, Payment::$paid_player_credit], true) && $amount === 0) {
            return $this->defaultAmountForField($field, $defaults);
        }

        if ($status === Payment::$debt && $amount === 0) {
            return $defaults['monthly'];
        }

        if ($status === Payment::$payment_agreement) {
            return $defaults['annuity'];
        }

        return $amount;
    }

    private function applyStatusToFollowingFields(
        array &$values,
        string $field,
        int $status,
        int $valueIfZero,
        bool $forceAmount = false,
        ?Payment $payment = null
    ): void {
        foreach ($this->paymentFieldsFrom($field) as $fieldName) {
            $amountField = Payment::amountFieldFor($fieldName);
            $currentAmount = array_key_exists($amountField, $values)
                ? $this->normalizeAmountValue($values[$amountField])
                : $this->normalizeAmountValue($payment ? $payment->{$amountField} : 0);

            $values[$fieldName] = $status;

            if ($forceAmount || $currentAmount === 0) {
                $values[$amountField] = $valueIfZero;
            }
        }
    }

    private function paymentFieldsFrom(string $field): array
    {
        $fields = Payment::paymentFields();
        $startIndex = array_search($field, $fields, true);

        if ($startIndex === false) {
            return [$field];
        }

        return array_slice($fields, $startIndex);
    }

    private function paymentDefaults(?Payment $payment = null): array
    {
        if ($payment?->inscription_id) {
            $payment->loadMissing('inscription.school.settingsValues');
        } elseif ($payment) {
            $payment->loadMissing('school.settingsValues');
        }

        $school = getSchool(auth()->user());
        $school->loadMissing('settingsValues');

        return [
            'inscription' => $this->paymentAmountResolver->inscriptionAmountForSchool($school),
            'monthly' => $payment
                ? $this->paymentAmountResolver->monthlyAmountForPayment($payment)
                : $this->paymentAmountResolver->monthlyAmountForSchool($school),
            'annuity' => $this->paymentAmountResolver->annuityAmountForSchool($school),
        ];
    }

    private function defaultAmountForField(string $field, array $defaults): int
    {
        return $field === 'enrollment'
            ? $defaults['inscription']
            : $defaults['monthly'];
    }

    private function normalizeStatusValue($status): int
    {
        $statusValue = (int) ($status ?? 0);

        return in_array($statusValue, Payment::STATUS_VALUES, true)
            ? $statusValue
            : 0;
    }

    private function syncPlayerCreditMovementsBeforeSave(Payment $payment, array $normalizedValues): void
    {
        $payment->loadMissing('inscription.player');
        $school = getSchool(auth()->user());

        foreach (Payment::paymentFields() as $field) {
            if (! array_key_exists($field, $normalizedValues)) {
                continue;
            }

            $previousStatus = (int) $payment->{$field};
            $currentStatus = (int) $normalizedValues[$field];

            if ($previousStatus === Payment::$paid_player_credit) {
                $this->playerCreditService->compensatePaymentDebit($payment, $field, (int) auth()->id());
            }

            if ($currentStatus !== Payment::$paid_player_credit) {
                continue;
            }

            abort_unless(
                $school->hasSchoolPermission('school.module.player_credits'),
                403,
                'El módulo de saldos a favor no está activo para esta escuela.'
            );

            $amountField = Payment::amountFieldFor($field);
            $amount = (int) ($normalizedValues[$amountField] ?? 0);
            $this->playerCreditService->applyPaymentDebit($payment, $field, $amount, $previousStatus, (int) auth()->id());
        }
    }

    private function normalizeAmountValue($amount): int
    {
        return max(0, (int) $amount);
    }
}
