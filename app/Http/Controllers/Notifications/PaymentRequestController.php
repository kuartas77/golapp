<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Repositories\PaymentRequestRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PaymentRequestController extends Controller
{
    public function __construct(private PaymentRequestRepository $repository)
    {

    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->repository->getPaymentRequestsQuery();
        }

        return view('payment-request.index');
    }

    public function proof(int $paymentRequest): Response|Application|ResponseFactory
    {
        $paymentRequestModel = $this->repository->findForCurrentSchoolOrFail($paymentRequest);
        $path = $paymentRequestModel->image;

        if (blank($path) || !Storage::disk('public')->exists($path)) {
            return response(null, 404);
        }

        return response(Storage::disk('public')->get($path))->withHeaders([
            'Content-Type' => Storage::disk('public')->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

}
