<?php

namespace App\Service\Payment;

use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Http\Request;

class PaymentControllerService
{
    public function __construct(private PaymentRepository $repository) {}

    public function filter(Request $request)
    {
        if ($request->has('dataRaw')) {
            $request->merge(['dataRaw' => $request->boolean('dataRaw')]);
        }

        if ($request->has('include_retired')) {
            $request->merge(['include_retired' => $request->boolean('include_retired')]);
        }

        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'training_group_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'string'],
            'player_search' => ['nullable', 'string', 'max:150'],
            'month' => ['nullable', 'string', 'in:'.implode(',', Payment::paymentFields())],
            'status' => ['nullable', 'string'],
            'dataRaw' => ['nullable', 'boolean'],
            'include_retired' => ['nullable', 'boolean'],
        ]);

        if ((int) $validated['year'] === (int) now()->year
            && empty($validated['training_group_id'])
            && empty($validated['category'])
            && empty($validated['player_search'])) {
            $message = 'Para el año actual selecciona un grupo, una categoría o busca un deportista.';

            return [
                'payload' => [
                    'message' => $message,
                    'errors' => [
                        'training_group_id' => [$message],
                    ],
                ],
                'status' => 422,
            ];
        }

        $request->merge(['school_id' => getSchool(auth()->user())->id]);

        return [
            'payload' => $this->repository->filter(
                $request,
                (bool) ($validated['include_retired'] ?? false),
                $request->filled('dataRaw')
            ),
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

        $catalog = PaymentStatusCatalog::toArray(
            $school->hasSchoolPermission('school.module.player_credits')
        );

        $catalog['capabilities'] = isAssistant()
            ? [
                'fields' => Payment::paymentFields(),
                'source_statuses' => [Payment::$debt, Payment::$paid_],
                'target_statuses' => [Payment::$paid, Payment::$paid_cash, Payment::$paid_deposit, Payment::$paid_, Payment::$disability],
                'bulk_update' => false,
            ]
            : [
                'fields' => Payment::paymentFields(),
                'source_statuses' => Payment::STATUS_VALUES,
                'target_statuses' => Payment::STATUS_VALUES,
                'bulk_update' => true,
            ];

        return $catalog;
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

        $source = isAssistant() ? 'assistant' : 'manual';
        if (! $this->repository->setPay($validated, $payment, $source)) {
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
