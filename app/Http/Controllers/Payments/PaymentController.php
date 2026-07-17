<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPaymentRequest;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Service\Payment\PaymentStatusCatalog;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * @var PaymentRepository
     */
    private $repository;

    public function __construct(PaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array|Application|Factory|View
     */
    public function index(Request $request)
    {
        view()->share('enabledPaymentOld', true);
        if ($request->ajax()) {
            if ($request->has('dataRaw')) {
                $request->merge(['dataRaw' => $request->boolean('dataRaw')]);
            }

            $validated = $request->validate([
                'year' => ['required', 'integer'],
                'training_group_id' => ['nullable', 'integer'],
                'category' => ['nullable', 'string'],
                'month' => ['nullable', 'string', 'in:'.implode(',', Payment::paymentFields())],
                'status' => ['nullable', 'string'],
                'player_name' => ['nullable', 'string'],
                'unique_code' => ['nullable', 'string'],
                'dataRaw' => ['nullable', 'boolean'],
            ]);

            if ((int) $validated['year'] === (int) now()->year
                && empty($validated['training_group_id'])
                && empty($validated['category'])) {
                return response()->json([
                    'message' => 'Para el año actual selecciona un grupo o una categoría.',
                    'errors' => [
                        'training_group_id' => ['Para el año actual selecciona un grupo o una categoría.'],
                    ],
                ], 422);
            }

            $request->merge(['school_id' => getSchool(auth()->user())->id]);

            return $this->repository->filter($request, false, $request->filled('dataRaw'));
        }

        $school = getSchool(auth()->user());
        view()->share('inscription_amount', data_get($school, 'settings.INSCRIPTION_AMOUNT', 70000));
        view()->share('monthly_payment', data_get($school, 'settings.MONTHLY_PAYMENT', 50000));
        view()->share('annuity', data_get($school, 'settings.ANNUITY', 48500));

        return view('payments.payment.index');
    }

    public function statusCatalog(): JsonResponse
    {
        $school = getSchool(auth()->user());

        return response()->json(PaymentStatusCatalog::toArray(
            $school->hasSchoolPermission('school.module.player_credits')
        ));
    }

    public function show($id, Request $request)
    {
        abort_unless($request->ajax(), 401);
        $payment = Payment::query()
            ->with(['inscription.player'])
            ->withTrashed()
            ->where('school_id', getSchool(auth()->user())->id)
            ->whereHas('inscription.player')
            ->find($id);

        if ($payment?->inscription?->player) {
            $payment->setRelation('player', $payment->inscription->player);
        }

        $payment?->setAttribute('inscription_deleted', (bool) $payment?->inscription?->trashed());
        $payment?->setAttribute('inscription_status_label', $payment?->inscription?->trashed() ? 'Retirada' : 'Activa');

        return $this->responseJson($payment);
    }

    /**
     * @param  Request  $request
     * @param  Payment  $payment
     */
    public function update(SetPaymentRequest $request, $id): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $payment = Payment::withTrashed()
            ->where('school_id', getSchool(auth()->user())->id)
            ->findOrFail($id);

        if ($this->repository->paymentBelongsToDeletedInscription($payment)) {
            return response()->json([
                'message' => PaymentRepository::RETIRED_INSCRIPTION_MESSAGE,
                'errors' => [
                    'payment' => [PaymentRepository::RETIRED_INSCRIPTION_MESSAGE],
                ],
            ], 422);
        }

        $isPay = $this->repository->setPay($request->validated(), $payment);
        if (! $isPay) {
            return $this->responseJson(false);
        }

        $payment = Payment::query()
            ->with(['inscription.player'])
            ->withTrashed()
            ->where('school_id', getSchool(auth()->user())->id)
            ->whereHas('inscription.player')
            ->findOrFail($id);

        if ($payment->inscription?->player) {
            $payment->setRelation('player', $payment->inscription->player);
        }

        $payment->setAttribute('inscription_deleted', (bool) $payment->inscription?->trashed());
        $payment->setAttribute('inscription_status_label', $payment->inscription?->trashed() ? 'Retirada' : 'Activa');

        return $this->responseJson($payment);
    }

    public function paymentStatuses(Request $request)
    {
        $payments = $this->repository->paymentsByStatus($request->only(['status']));

        return view('payments.status.index', compact('payments'));
    }
}
