<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Payment;
use App\Service\Kpi\KpiCacheService;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        $this->invalidate($payment);
    }

    public function deleted(Payment $payment): void
    {
        $this->invalidate($payment);
    }

    public function restored(Payment $payment): void
    {
        $this->invalidate($payment);
    }

    public function forceDeleted(Payment $payment): void
    {
        $this->invalidate($payment);
    }

    private function invalidate(Payment $payment): void
    {
        $schoolId = (int) $payment->school_id;

        if ($schoolId <= 0) {
            return;
        }

        app(KpiCacheService::class)->invalidateSchool($schoolId);
    }
}
