<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Service\Payment\MonthlyPaymentReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonthlyPaymentReceiptController extends Controller
{
    public function __construct(private MonthlyPaymentReceiptService $receiptService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->has('draw')) {
            return $this->receiptService->datatableRows($request);
        }

        return $this->responseJson($this->receiptService->receiptRows($request));
    }

    public function show(Payment $payment, string $month)
    {
        return $this->receiptService->streamReceipt($payment, $month);
    }
}
