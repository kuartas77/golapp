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
        if ($request->ajax()) {
            return $this->repository->filter($request);
        }

        $school = getSchool(auth()->user());
        view()->share('inscription_amount', $school->settings['INSCRIPTION_AMOUNT'] ?? 70000);
        view()->share('monthly_payment', $school->settings['MONTHLY_PAYMENT'] ?? 50000);
        view()->share('annuity', $school->settings['ANNUITY'] ?? 48333);

        return view('payments.payment.index');
    }

    /**
     * @param Request $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function update(SetPaymentRequest $request, Payment $payment): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $isPay = $this->repository->setPay($request->validated(), $payment);
        return $this->responseJson($isPay);
    }
}
