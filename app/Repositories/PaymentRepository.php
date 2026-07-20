<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Payment;
use App\Service\Payment\PaymentAnalyticsService;
use App\Service\Payment\PaymentListingService;
use App\Service\Payment\PaymentMutationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class PaymentRepository
{
    public const RETIRED_INSCRIPTION_MESSAGE = 'La inscripción está retirada; reactívala antes de modificar pagos o asistencias.';

    public function __construct(
        private PaymentAnalyticsService $analyticsService,
        private PaymentListingService $listingService,
        private PaymentMutationService $mutationService
    ) {
        //
    }

    /**
     * @param  false  $deleted
     */
    public function filter($request, bool $deleted = false, $raw = false): array
    {
        return $this->listingService->filter($request, $deleted, $raw);
    }

    /**
     * @param  false  $deleted
     */
    public function filterSelect(array $params, bool $deleted = false, bool $raw = false): Builder
    {
        return $this->listingService->filterSelect($params, $deleted, $raw);
    }

    public function filterSelectRaw(array $params, bool $deleted = false)
    {
        return $this->listingService->filterSelectRaw($params, $deleted);
    }

    public function setPay(array $values, Payment $payment, string $source = 'manual'): bool
    {
        return $this->mutationService->setPay($values, $payment, $source);
    }

    public function bulkUpdate(array $validated): array
    {
        return $this->mutationService->bulkUpdate($validated);
    }

    public function history(Payment $payment): array
    {
        return $this->mutationService->history($payment);
    }

    public function decoratePayment(Payment $payment): Payment
    {
        return $this->listingService->decoratePayment($payment);
    }

    public function paymentBelongsToDeletedInscription(Payment $payment): bool
    {
        return $this->mutationService->paymentBelongsToDeletedInscription($payment);
    }

    public function dataGraphicsYear(int $year = 0): Collection
    {
        return $this->analyticsService->dataGraphicsYear($year);
    }

    public function queryPaymentsDueByMonth(int $schoolId, ?int $year = null, ?int $month = null, bool $onlyDue = true): QueryBuilder
    {
        return $this->analyticsService->queryPaymentsDueByMonth($schoolId, $year, $month, $onlyDue);
    }

    public function paymentsByStatus(array $params)
    {
        return $this->analyticsService->paymentsByStatus($params);
    }

    public function dataGraphicsDashboard()
    {
        return $this->analyticsService->dataGraphicsDashboard();
    }
}
