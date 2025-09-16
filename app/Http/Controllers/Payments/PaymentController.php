<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPaymentRequest;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
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
     * @param Request $request
     * @return array|Application|Factory|View
     */
    public function index(Request $request)
    {
        view()->share('enabledPaymentOld', false);
        if ($request->ajax()) {
            $request->merge(['school_id' => getSchool(auth()->user())->id]);
            return $this->repository->filter($request, false, $request->filled('dataRaw'));
        }

        $school = getSchool(auth()->user());
        view()->share('inscription_amount', data_get($school, 'settings.INSCRIPTION_AMOUNT', 70000));
        view()->share('monthly_payment', data_get($school, 'settings.MONTHLY_PAYMENT', 50000));
        view()->share('annuity', data_get($school, 'settings.ANNUITY', 48500));

        return view('payments.payment.index');
    }

    public function show($id, Request $request)
    {
        abort_unless($request->ajax(), 401);
        $payment = Payment::query()->with(['player'])->withTrashed()->whereHas('player')->find($id);
        return $this->responseJson($payment);
    }

    /**
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function update(SetPaymentRequest $request, $id): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $payment = Payment::withTrashed()->find($id);
        $isPay = $this->repository->setPay($request->validated(), $payment);
        return $this->responseJson($isPay);
    }

    public function paymentStatuses(Request $request)
    {
        $payments = $this->repository->paymentsByStatus($request->only(['status']));
        return view('payments.status.index', compact('payments'));
    }
}
