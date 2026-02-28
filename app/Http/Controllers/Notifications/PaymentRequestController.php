<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Repositories\PaymentRequestRepository;
use Illuminate\Http\Request;

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

}
