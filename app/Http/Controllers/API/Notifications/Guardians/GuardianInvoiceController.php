<?php

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\API\Notifications\Guardians\Concerns\ResolvesGuardianPlayers;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\GuardianPaymentInvoiceRequest;
use App\Http\Resources\API\Notification\Invoices\InvoiceCollection;
use App\Http\Resources\API\Notification\Invoices\InvoiceResource;
use App\Http\Resources\API\Notification\Invoices\InvoiceStatistcsResource;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRequestRepository;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianInvoiceController extends Controller
{
    use ResolvesGuardianPlayers;

    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private InvoiceRepository $invoiceRepository,
        private PaymentRequestRepository $paymentRequestRepository
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new InvoiceCollection($this->invoiceRepository->invoicesForPlayers($this->eligiblePlayers($request))))->resolve($request),
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new InvoiceStatistcsResource($this->invoiceRepository->statisticsForPlayers($this->eligiblePlayers($request))))->resolve($request),
        ]);
    }

    public function show(Request $request, int $invoice): JsonResponse
    {
        return response()->json([
            'data' => (new InvoiceResource($this->invoiceRepository->findPlayersInvoiceOrFail($this->eligiblePlayers($request), $invoice)))->resolve($request),
        ]);
    }

    public function payment(GuardianPaymentInvoiceRequest $request): InvoiceResource|JsonResponse
    {
        $invoice = $this->paymentRequestRepository->createGuardianPaymentRequest(
            $request->validated(),
            $this->eligiblePlayers($request)
        );

        if (!$invoice) {
            return response()->json([
                'data' => [
                    'message' => 'No fue posible registrar el comprobante de pago.',
                ],
            ], 500);
        }

        return response()->json([
            'data' => (new InvoiceResource($invoice))->resolve($request),
        ]);
    }
}
