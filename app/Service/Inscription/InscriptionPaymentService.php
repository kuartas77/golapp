<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\PaymentChangeLog;
use App\Models\School;
use App\Models\Setting;
use App\Models\TrainingGroup;
use App\Service\PaymentAmountResolver;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class InscriptionPaymentService
{
    /** @var array<int, School> */
    private array $schools = [];

    /** @var array<string, TrainingGroup|null> */
    private array $trainingGroups = [];

    public function __construct(private PaymentAmountResolver $paymentAmountResolver) {}

    /** @param array<string, mixed> $requestData */
    public function prepareMonthlyPaymentData(array &$requestData, ?School $school = null): void
    {
        $school ??= $this->schoolFor((int) $requestData['school_id']);

        if ($school->training_group_monthly_payment_enabled) {
            $trainingGroup = $this->trainingGroupForSchool(
                (int) $school->id,
                (int) $requestData['training_group_id']
            );

            if (! $trainingGroup) {
                throw ValidationException::withMessages([
                    'training_group_id' => 'Selecciona un grupo principal válido para definir la tarifa mensual.',
                ]);
            }

            if ($trainingGroup->name !== 'Provisional' && ! $trainingGroup->monthly_payment_amount) {
                throw ValidationException::withMessages([
                    'training_group_id' => 'El grupo seleccionado no tiene una tarifa mensual configurada.',
                ]);
            }

            $requestData['monthly_payment_type'] = Inscription::TRAINING_GROUP_MONTHLY_PAYMENT;
            $requestData['monthly_payment_amount'] = $trainingGroup->name === 'Provisional'
                ? null
                : (int) $trainingGroup->monthly_payment_amount;
            $requestData['brother_payment'] = false;

            return;
        }

        $type = $this->paymentAmountResolver->normalizeMonthlyPaymentType(
            data_get($requestData, 'monthly_payment_type'),
            (bool) data_get($requestData, 'brother_payment', false)
        );

        $requestData['monthly_payment_type'] = $type;
        $requestData['monthly_payment_amount'] = $this->paymentAmountResolver
            ->monthlyAmountForSchoolByType($school, $type);
        $requestData['brother_payment'] = $type === Setting::BROTHER_MONTHLY_PAYMENT;
    }

    public function schoolFor(int $schoolId): School
    {
        return $this->schools[$schoolId] ??= School::query()
            ->with('settingsValues')
            ->findOrFail($schoolId);
    }

    /** @param array<string, mixed> $requestData */
    public function preserveMonthlyPaymentData(array &$requestData, Inscription $inscription): void
    {
        $requestData['monthly_payment_type'] = $inscription->monthly_payment_type;
        $requestData['monthly_payment_amount'] = $inscription->monthly_payment_amount;
        $requestData['brother_payment'] = $inscription->brother_payment;
    }

    /** @param array<string, mixed> $requestData */
    public function shouldInitializeGroupTariff(
        array $requestData,
        Inscription $inscription,
        ?School $school = null
    ): bool {
        if ($inscription->monthly_payment_amount !== null) {
            return false;
        }

        $school ??= $this->schoolFor((int) $requestData['school_id']);

        if (! $school->training_group_monthly_payment_enabled) {
            return false;
        }

        $originalGroup = $this->trainingGroupForSchool(
            (int) $school->id,
            $inscription->training_group_id ? (int) $inscription->training_group_id : null,
            true
        );
        $newGroup = $this->trainingGroupForSchool(
            (int) $school->id,
            (int) $requestData['training_group_id']
        );

        return $originalGroup?->name === 'Provisional'
            && $newGroup?->name !== 'Provisional'
            && (int) $newGroup?->monthly_payment_amount > 0;
    }

    private function trainingGroupForSchool(
        int $schoolId,
        ?int $trainingGroupId,
        bool $withTrashed = false
    ): ?TrainingGroup {
        if (! $trainingGroupId) {
            return null;
        }

        $key = $schoolId.':'.$trainingGroupId.':'.(int) $withTrashed;

        if (array_key_exists($key, $this->trainingGroups)) {
            return $this->trainingGroups[$key];
        }

        $query = TrainingGroup::query()
            ->when($withTrashed, fn ($query) => $query->withTrashed())
            ->where('school_id', $schoolId)
            ->where('is_complementary', false);

        return $this->trainingGroups[$key] = $query->find($trainingGroupId);
    }

    public function applyScholarshipMonthlyPayments(Inscription $inscription): void
    {
        $this->syncScholarshipPayments($inscription);
    }

    public function syncScholarshipPayments(Inscription $inscription): void
    {
        /** @var Payment|null $payment */
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
        $collectibleStatuses = [
            Payment::$pending,
            Payment::$debt,
            Payment::$paid_,
            Payment::$payment_agreement,
            Payment::$scholarship_recipient,
            Payment::$no_application,
        ];
        $isFullScholarship = $inscription->scholarship
            && (int) $inscription->scholarship_percentage === Inscription::FULL_SCHOLARSHIP_PERCENTAGE;
        $enrollmentAmount = $this->paymentAmountResolver
            ->payableInscriptionAmountForInscription($inscription);
        $monthlyAmount = $this->paymentAmountResolver
            ->payableMonthlyAmountForInscription($inscription);
        $changes = [];

        $this->collectScholarshipPaymentChange(
            $payment,
            'enrollment',
            $enrollmentAmount,
            $isFullScholarship,
            $preservedStatuses,
            $collectibleStatuses,
            $changes
        );

        foreach (config('variables.KEY_INDEX_MONTHS', []) as $monthNumber => $field) {
            if ((int) $payment->year === (int) $startDate->year && (int) $monthNumber < (int) $startDate->month) {
                continue;
            }

            $this->collectScholarshipPaymentChange(
                $payment,
                $field,
                $monthlyAmount,
                $isFullScholarship,
                $preservedStatuses,
                $collectibleStatuses,
                $changes,
                (int) $monthNumber
            );
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
                'new_status' => $change['new_status'],
                'old_amount' => $change['old_amount'],
                'new_amount' => $change['new_amount'],
                'source' => 'inscription_scholarship',
            ]);
        }
    }

    public function recalculateCollectibleMonthlyPaymentAmounts(
        Inscription $inscription,
        string $source = 'inscription_tariff'
    ): void {
        /** @var Payment|null $payment */
        $payment = $inscription->payments()
            ->where('year', $inscription->year)
            ->first();

        if (! $payment) {
            return;
        }

        $monthlyAmount = $this->paymentAmountResolver->payableMonthlyAmountForInscription($inscription);
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
                'source' => $source,
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
        // La beca del 50 % mantiene Pendiente/Debe para que el saldo reducido siga en cobranza.
        $isFullScholarship = $inscription->scholarship
            && (int) $inscription->scholarship_percentage === Inscription::FULL_SCHOLARSHIP_PERCENTAGE;
        $paymentValue = $isFullScholarship
            ? (string) Payment::$scholarship_recipient
            : (string) Payment::$pending;
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

        if (! $isFullScholarship) {
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
     * @param  array<int, int>  $collectibleStatuses
     * @param  array<string, array{old_status: int, new_status: int, old_amount: int, new_amount: int}>  $changes
     */
    private function collectScholarshipPaymentChange(
        Payment $payment,
        string $field,
        int $payableAmount,
        bool $isFullScholarship,
        array $preservedStatuses,
        array $collectibleStatuses,
        array &$changes,
        ?int $monthNumber = null
    ): void {
        $oldStatus = (int) $payment->{$field};

        if (
            in_array($oldStatus, $preservedStatuses, true)
            || ($oldStatus === Payment::$no_application && ! $isFullScholarship)
            || ! in_array($oldStatus, $collectibleStatuses, true)
        ) {
            return;
        }

        $amountField = Payment::amountFieldFor($field);

        if (! $amountField) {
            return;
        }

        $oldAmount = (int) $payment->{$amountField};
        $newStatus = $oldStatus;
        $newAmount = $payableAmount;

        if ($isFullScholarship) {
            // La beca del 100 % condona todo el valor y sale de los flujos de cobro como estado Becado.
            $newStatus = (int) Payment::$scholarship_recipient;
            $newAmount = 0;
        } elseif ($oldStatus === Payment::$scholarship_recipient) {
            $newStatus = $this->restoredCollectibleStatus($payment, $field, $monthNumber);
        }

        if ($oldStatus === $newStatus && $oldAmount === $newAmount) {
            return;
        }

        $changes[$field] = [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'old_amount' => $oldAmount,
            'new_amount' => $newAmount,
        ];
        $payment->{$field} = $newStatus;
        $payment->{$amountField} = $newAmount;
    }

    private function restoredCollectibleStatus(Payment $payment, string $field, ?int $monthNumber): int
    {
        if ($field === 'enrollment') {
            return Payment::$debt;
        }

        $paymentYear = (int) $payment->year;
        $currentYear = (int) now()->year;

        if ($paymentYear !== $currentYear) {
            return $paymentYear < $currentYear
                ? Payment::$debt
                : Payment::$pending;
        }

        return (int) $monthNumber <= (int) now()->month
            ? Payment::$debt
            : Payment::$pending;
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
        $inscriptionAmount = $this->paymentAmountResolver->payableInscriptionAmountForInscription($inscription);
        $monthlyAmount = $this->paymentAmountResolver->payableMonthlyAmountForInscription($inscription);
        $monthField = config("variables.KEY_INDEX_MONTHS.{$actualMonth}");

        $dataPayment['enrollment'] = (string) Payment::$debt;
        $dataPayment['enrollment_amount'] = $inscriptionAmount;

        if ($monthField) {
            $dataPayment[$monthField] = (string) Payment::$debt;
            $dataPayment["{$monthField}_amount"] = $monthlyAmount;
        }
    }
}
