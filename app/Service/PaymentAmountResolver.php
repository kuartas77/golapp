<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\School;
use App\Models\Setting;

class PaymentAmountResolver
{
    private const DEFAULT_INSCRIPTION_AMOUNT = 70000;

    private const DEFAULT_MONTHLY_PAYMENT = 50000;

    private const DEFAULT_ANNUITY = 48333;

    public function inscriptionAmountForSchool(School $school): int
    {
        return $this->normalizeAmountValue(
            $this->settingValue($school, Setting::INSCRIPTION_AMOUNT, self::DEFAULT_INSCRIPTION_AMOUNT)
        );
    }

    public function monthlyAmountForSchool(School $school): int
    {
        return $this->normalizeAmountValue(
            $this->settingValue($school, Setting::MONTHLY_PAYMENT, self::DEFAULT_MONTHLY_PAYMENT)
        );
    }

    public function brotherMonthlyAmountForSchool(School $school): int
    {
        $monthlyAmount = $this->monthlyAmountForSchool($school);
        $brotherAmount = $this->settingValue($school, Setting::BROTHER_MONTHLY_PAYMENT);

        if ($brotherAmount === null || $brotherAmount === '') {
            return $monthlyAmount;
        }

        return $this->normalizeAmountValue($brotherAmount);
    }

    public function annuityAmountForSchool(School $school): int
    {
        return $this->normalizeAmountValue(
            $this->settingValue($school, Setting::ANNUITY, self::DEFAULT_ANNUITY)
        );
    }

    public function monthlyAmountForInscription(Inscription $inscription): int
    {
        $inscription->loadMissing('school.settingsValues');

        if (!$inscription->school) {
            return self::DEFAULT_MONTHLY_PAYMENT;
        }

        return $inscription->brother_payment
            ? $this->brotherMonthlyAmountForSchool($inscription->school)
            : $this->monthlyAmountForSchool($inscription->school);
    }

    public function monthlyAmountForPayment(Payment $payment): int
    {
        $payment->loadMissing(['inscription.school.settingsValues', 'school.settingsValues']);

        if ($payment->inscription) {
            return $this->monthlyAmountForInscription($payment->inscription);
        }

        if ($payment->school) {
            return $this->monthlyAmountForSchool($payment->school);
        }

        return self::DEFAULT_MONTHLY_PAYMENT;
    }

    private function settingValue(School $school, string $key, $default = null)
    {
        $school->loadMissing('settingsValues');

        return data_get($school, "settings.{$key}", $default);
    }

    private function normalizeAmountValue($amount): int
    {
        return max(0, (int) $amount);
    }
}
