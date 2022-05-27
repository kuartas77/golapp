<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PlayerRepository;
use Illuminate\Contracts\View\Factory;
use App\Repositories\PaymentRepository;
use Illuminate\Contracts\Foundation\Application;

class HomeController extends Controller
{

    private PaymentRepository $paymentRepository;
    private PlayerRepository $playerRepository;

    /**
     * Create a new controller instance.
     *
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(PaymentRepository $paymentRepository, PlayerRepository $playerRepository)
    {
        $this->middleware('auth');
        $this->paymentRepository = $paymentRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return Application|Factory|JsonResponse|View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'current' => $this->paymentRepository->dataGraphicsYear(now()->year),
                'past' => $this->paymentRepository->dataGraphicsYear(now()->subYear()->year)
            ]);
        }
        $birthdays = $this->playerRepository->birthdayToday()->count();

        return view('home', compact('birthdays'));
    }

    public function birthDays()
    {
        $birthdays = $this->playerRepository->birthdayToday();

        return view('players.birthdays', compact('birthdays'));
    }
}
