<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentBulkUpdateRequest;
use App\Http\Requests\SetPaymentRequest;
use App\Models\Payment;
use App\Service\Payment\PaymentControllerService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentControllerService $payments)
    {
    }

    /**
     * @return array|Application|Factory|View
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->expectsJson() || $request->is('api/*')) {
            $result = $this->payments->filter($request);

            return response()->json($result['payload'], $result['status']);
        }

        abort(404);
    }

    public function statusCatalog(): JsonResponse
    {
        return response()->json($this->payments->statusCatalog());
    }

    public function bulkUpdate(PaymentBulkUpdateRequest $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'data' => $this->payments->bulkUpdate($request->validated()),
        ]);
    }

    public function history(Payment $payment): JsonResponse
    {
        return response()->json([
            'data' => $this->payments->history($payment),
        ]);
    }

    public function show($id, Request $request)
    {
        abort_unless($request->ajax(), 401);
        return $this->responseJson($this->payments->decoratedPayment((int) $id));
    }

    /**
     * @param  Request  $request
     * @param  Payment  $payment
     */
    public function update(SetPaymentRequest $request, $id): JsonResponse
    {
        abort_unless($request->ajax(), 401);
        $result = $this->payments->update((int) $id, $request->validated());

        if ($result['wrap_data']) {
            return $this->responseJson($result['payload'], $result['status']);
        }

        return response()->json($result['payload'], $result['status']);
    }

    public function paymentStatuses(Request $request)
    {
        $payments = $this->payments->paymentsByStatus($request->only(['status']));

        return view('payments.status.index', compact('payments'));
    }
}
