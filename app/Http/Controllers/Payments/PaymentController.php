<?php

namespace App\Http\Controllers\Payments;

use App\Models\Payment;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use App\Repositories\PaymentRepository;
use App\Http\Requests\SetPaymentRequest;
use Illuminate\Contracts\Foundation\Application;

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
