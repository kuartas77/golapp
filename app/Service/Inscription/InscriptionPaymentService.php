<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\PaymentChangeLog;
use App\Models\School;
use App\Models\Setting;
use App\Service\PaymentAmountResolver;
use Illuminate\Support\Carbon;

class InscriptionPaymentService
{
    public function __construct(private PaymentAmountResolver $paymentAmountResolver) {}

    /** @param array<string, mixed> $requestData */
    public function prepareMonthlyPaymentData(array &$requestData): void
    {
        $school = School::query()
            ->with('settingsValues')
            ->findOrFail($requestData['school_id']);

        $type = $this->paymentAmountResolver->normalizeMonthlyPaymentType(
            data_get($requestData, 'monthly_payment_type'),
            (bool) data_get($requestData, 'brother_payment', false)
        );

        $requestData['monthly_payment_type'] = $type;
        $requestData['monthly_payment_amount'] = $this->paymentAmountResolver
            ->monthlyAmountForSchoolByType($school, $type);
        $requestData['brother_payment'] = $type === Setting::BROTHER_MONTHLY_PAYMENT;
    }

    /** @param array<string, mixed> $requestData */
    public function preserveMonthlyPaymentData(array &$requestData, Inscription $inscription): void
    {
        $requestData['monthly_payment_type'] = $inscription->monthly_payment_type;
        $requestData['monthly_payment_amount'] = $inscription->monthly_payment_amount;
        $requestData['brother_payment'] = $inscription->brother_payment;
    }

    public function applyScholarshipMonthlyPayments(Inscription $inscription): void
    {
        $payment = $inscription->payments()
            ->where('year', $inscription->year)
            ->first();

        if (! $payment) {
            return;
        }

        $startDate = Carbon::parse($inscription->start_date);
        $preservedStatuses = [
            Payment::$paid,
            Payment::$paid_cash,
            Payment::$paid_deposit,
            Payment::$annuity_payment_deposit,
            Payment::$annuity_payment_cash,
            Payment::$paid_player_credit,
            Payment::$temporary_retirement,
            Payment::$permanent_retirement,
        ];
        $changes = [];

        $this->collectScholarshipPaymentChange($payment, 'enrollment', $preservedStatuses, $changes);

        foreach (config('variables.KEY_INDEX_MONTHS', []) as $monthNumber => $field) {
            if ((int) $payment->year === (int) $startDate->year && (int) $monthNumber < (int) $startDate->month) {
                continue;
            }

            $this->collectScholarshipPaymentChange($payment, $field, $preservedStatuses, $changes);
        }

        if ($changes === []) {
            return;
        }

        $payment->save();

        foreach ($changes as $field => $change) {
            PaymentChangeLog::query()->create([
                'school_id' => $payment->school_id,
                'payment_id' => $payment->id,
                'inscription_id' => $payment->inscription_id,
                'changed_by' => auth()->id(),
                'year' => $payment->year,
                'field' => $field,
                'old_status' => $change['old_status'],
                'new_status' => Payment::$scholarship_recipient,
                'old_amount' => $change['old_amount'],
                'new_amount' => 0,
                'source' => 'inscription_scholarship',
            ]);
        }
    }

    public function recalculateCollectibleMonthlyPaymentAmounts(Inscription $inscription): void
    {
        $payment = $inscription->payments()
            ->where('year', $inscription->year)
            ->first();

        if (! $payment) {
            return;
        }

        $monthlyAmount = $this->paymentAmountResolver->monthlyAmountForInscription($inscription);
        $collectibleStatuses = [
            Payment::$pending,
            Payment::$debt,
            Payment::$paid_,
            Payment::$payment_agreement,
        ];
        $changes = [];

        foreach (config('variables.KEY_INDEX_MONTHS', []) as $field) {
            $amountField = Payment::amountFieldFor($field);

            if (! $amountField || ! in_array((int) $payment->{$field}, $collectibleStatuses, true)) {
                continue;
            }

            $oldAmount = (int) $payment->{$amountField};

            if ($oldAmount === $monthlyAmount) {
                continue;
            }

            $changes[$field] = [
                'status' => (int) $payment->{$field},
                'old_amount' => $oldAmount,
                'new_amount' => $monthlyAmount,
            ];
            $payment->{$amountField} = $monthlyAmount;
        }

        if ($changes === []) {
            return;
        }

        $payment->save();

        foreach ($changes as $field => $change) {
            PaymentChangeLog::query()->create([
                'school_id' => $payment->school_id,
                'payment_id' => $payment->id,
                'inscription_id' => $payment->inscription_id,
                'changed_by' => auth()->id(),
                'year' => $payment->year,
                'field' => $field,
                'old_status' => $change['status'],
                'new_status' => $change['status'],
                'old_amount' => $change['old_amount'],
                'new_amount' => $change['new_amount'],
                'source' => 'inscription_tariff',
            ]);
        }
    }

    public function restoreRetiredPendingMonths(Inscription $inscription): void
    {
        $year = (int) Carbon::parse($inscription->start_date)->year;
        $payments = $inscription->payments()->where('year', $year)->get();

        foreach ($payments as $payment) {
            $shouldSave = false;

            foreach (config('variables.KEY_INDEX_MONTHS', []) as $field) {
                if ((int) $payment->{$field} !== Payment::$permanent_retirement) {
                    continue;
                }

                $payment->{$field} = (string) Payment::$pending;
                $shouldSave = true;
            }

            if ($shouldSave) {
                $payment->save();
            }
        }
    }

    public function buildInitialPaymentData(Inscription $inscription, Carbon $startDate): array
    {
        $paymentValue = $inscription->scholarship ? (string) Payment::$scholarship_recipient : (string) Payment::$pending;
        $dataPayment = [
            'inscription_id' => $inscription->id,
            'year' => (int) $startDate->year,
            'training_group_id' => $inscription->training_group_id,
            'school_id' => $inscription->school_id,
            'unique_code' => $inscription->unique_code,
            'enrollment' => $paymentValue,
            'january' => $paymentValue,
            'february' => $paymentValue,
            'march' => $paymentValue,
            'april' => $paymentValue,
            'may' => $paymentValue,
            'june' => $paymentValue,
            'july' => $paymentValue,
            'august' => $paymentValue,
            'september' => $paymentValue,
            'october' => $paymentValue,
            'november' => $paymentValue,
            'december' => $paymentValue,
        ];

        if ((int) $startDate->month > 1) {
            $this->checkMonthValue((int) $startDate->month, $paymentValue, $dataPayment);
        }

        if (! $inscription->scholarship) {
            $this->debtMonth($inscription, (int) $startDate->month, $dataPayment);
        }

        return $dataPayment;
    }

    public function markFutureCollectibleMonthsAsRetired(Payment $payment): void
    {
        foreach (config('variables.KEY_INDEX_MONTHS', []) as $field) {
            if (in_array((int) $payment->{$field}, [Payment::$pending, Payment::$debt], true)) {
                $payment->{$field} = (string) Payment::$permanent_retirement;
            }
        }
    }

    /**
     * @param  array<int, int>  $preservedStatuses
     * @param  array<string, array{old_status: int, old_amount: int}>  $changes
     */
    private function collectScholarshipPaymentChange(Payment $payment, string $field, array $preservedStatuses, array &$changes): void
    {
        if (in_array((int) $payment->{$field}, $preservedStatuses, true)) {
            return;
        }

        $amountField = Payment::amountFieldFor($field);

        if (! $amountField) {
            return;
        }

        $oldStatus = (int) $payment->{$field};
        $oldAmount = (int) $payment->{$amountField};

        if ($oldStatus === Payment::$scholarship_recipient && $oldAmount === 0) {
            return;
        }

        $changes[$field] = [
            'old_status' => $oldStatus,
            'old_amount' => $oldAmount,
        ];
        $payment->{$field} = Payment::$scholarship_recipient;
        $payment->{$amountField} = 0;
    }

    /** @param array<string, int|string> $dataPayment */
    private function checkMonthValue(int $actualMonth, string $value, array &$dataPayment): void
    {
        foreach (range(1, $actualMonth) as $monthNumber) {
            $field = config("variables.KEY_INDEX_MONTHS.{$monthNumber}");

            if (! $field) {
                continue;
            }

            $dataPayment[$field] = $actualMonth === $monthNumber
                ? $value
                : (string) Payment::$no_application;
        }
    }

    /** @param array<string, int|string> $dataPayment */
    private function debtMonth(Inscription $inscription, int $actualMonth, array &$dataPayment): void
    {
        $inscriptionAmount = data_get($inscription->school->settings, 'INSCRIPTION_AMOUNT', 70000);
        $monthlyAmount = $this->paymentAmountResolver->monthlyAmountForInscription($inscription);
        $monthField = config("variables.KEY_INDEX_MONTHS.{$actualMonth}");

        $dataPayment['enrollment'] = (string) Payment::$debt;
        $dataPayment['enrollment_amount'] = $inscriptionAmount;

        if ($monthField) {
            $dataPayment[$monthField] = (string) Payment::$debt;
            $dataPayment["{$monthField}_amount"] = $monthlyAmount;
        }
    }
}
