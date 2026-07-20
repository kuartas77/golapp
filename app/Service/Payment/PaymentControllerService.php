<?php

namespace App\Service\Payment;

use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Http\Request;

class PaymentControllerService
{
    public function __construct(private PaymentRepository $repository)
    {
    }

    public function filter(Request $request)
    {
        if ($request->has('dataRaw')) {
            $request->merge(['dataRaw' => $request->boolean('dataRaw')]);
        }

        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'training_group_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'string'],
            'month' => ['nullable', 'string', 'in:' . implode(',', Payment::paymentFields())],
            'status' => ['nullable', 'string'],
            'dataRaw' => ['nullable', 'boolean'],
        ]);

        if ((int) $validated['year'] === (int) now()->year
            && empty($validated['training_group_id'])
            && empty($validated['category'])) {
            return [
                'payload' => [
                    'message' => 'Para el año actual selecciona un grupo o una categoría.',
                    'errors' => [
                        'training_group_id' => ['Para el año actual selecciona un grupo o una categoría.'],
                    ],
                ],
                'status' => 422,
            ];
        }

        $request->merge(['school_id' => getSchool(auth()->user())->id]);

        return [
            'payload' => $this->repository->filter($request, false, $request->filled('dataRaw')),
            'status' => 200,
        ];
    }

    public function viewData(): array
    {
        $school = getSchool(auth()->user());

        return [
            'inscription_amount' => data_get($school, 'settings.INSCRIPTION_AMOUNT', 70000),
            'monthly_payment' => data_get($school, 'settings.MONTHLY_PAYMENT', 50000),
            'annuity' => data_get($school, 'settings.ANNUITY', 48500),
        ];
    }

    public function statusCatalog(): array
    {
        $school = getSchool(auth()->user());

        return PaymentStatusCatalog::toArray(
            $school->hasSchoolPermission('school.module.player_credits')
        );
    }

    public function bulkUpdate(array $validated): array
    {
        return $this->repository->bulkUpdate($validated);
    }

    public function history(Payment $payment): array
    {
        abort_unless((int) $payment->school_id === (int) getSchool(auth()->user())->id, 404);

        return $this->repository->history($payment);
    }

    public function paymentsByStatus(array $filters)
    {
        return $this->repository->paymentsByStatus($filters);
    }

    public function decoratedPayment(int $id): ?Payment
    {
        $payment = Payment::query()
            ->with(['inscription.player'])
            ->withTrashed()
            ->where('school_id', getSchool(auth()->user())->id)
            ->whereHas('inscription.player')
            ->find($id);

        return $payment ? $this->repository->decoratePayment($payment) : null;
    }

    public function update(int $id, array $validated): array
    {
        $payment = Payment::withTrashed()
            ->where('school_id', getSchool(auth()->user())->id)
            ->findOrFail($id);

        if ($this->repository->paymentBelongsToDeletedInscription($payment)) {
            return [
                'payload' => [
                    'message' => PaymentRepository::RETIRED_INSCRIPTION_MESSAGE,
                    'errors' => [
                        'payment' => [PaymentRepository::RETIRED_INSCRIPTION_MESSAGE],
                    ],
                ],
                'status' => 422,
                'wrap_data' => false,
            ];
        }

        if (! $this->repository->setPay($validated, $payment)) {
            return [
                'payload' => false,
                'status' => 200,
                'wrap_data' => true,
            ];
        }

        return [
            'payload' => $this->decoratedPayment($id),
            'status' => 200,
            'wrap_data' => true,
        ];
    }
}
