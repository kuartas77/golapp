<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\Factory;
use App\Repositories\PaymentRepository;
use Illuminate\Contracts\Foundation\Application;

class HomeController extends Controller
{

    private PaymentRepository $paymentRepository;

    /**
     * Create a new controller instance.
     *
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->middleware('auth');
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return Application|Factory|JsonResponse|View
     */
    public function index(Request $request)
    {
        //  Auth::loginUsingId(6);
        //  dd(auth()->user());

        // $user = User::find(6);
        // dd($user->school);
        //Auth::loginUsingId(1);
        //auth()->user()->assignRole('administrador');

        if ($request->ajax()) {
            return response()->json([
                'current' => $this->paymentRepository->dataGraphicsYear(now()->year),
                'past' => $this->paymentRepository->dataGraphicsYear(now()->subYear()->year)
            ]);
        }
        return view('home');
    }
}
