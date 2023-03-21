<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;
use App\Repositories\AssistRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\TrainingGroupRepository;

class HistoricController extends Controller
{

    private TrainingGroupRepository $groupRepository;
    private AssistRepository $assistRepository;
    private PaymentRepository $paymentRepository;

    public function __construct(
        TrainingGroupRepository $groupRepository,
        AssistRepository        $assistRepository,
        PaymentRepository       $paymentRepository
    )
    {
        $this->groupRepository = $groupRepository;
        $this->assistRepository = $assistRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function assists(Request $request): View|Factory|JsonResponse|Application
    {
        if ($request->ajax()) {
            return datatables()->collection($this->groupRepository->historicAssistData())->toJson();
        }

        return view('assists.historic.index');
    }

    public function assistsGroup(Request $request, $trainingGroup, $year): View|Factory|array|Application
    {
        if ($request->ajax()) {
            return $this->assistRepository->search($request->only(['training_group_id', 'year', 'month']), true);
        }
        $trainingGroup = TrainingGroup::query()->onlyTrashedRelations()->findOrFail($trainingGroup);

        $months = DB::table('assists')->select('month')
            ->where('year', $year)
            ->where('training_group_id', $trainingGroup->id)
            ->pluck('month', 'month');

        view()->share('year', $year);
        view()->share('monthsG', $months);
        view()->share('trainingGroup', $trainingGroup);
        return view('assists.historic.show');
    }

    public function payments(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
                ->collection($this->groupRepository->historicPaymentData())
                ->toJson();
        }

        return view('payments.historic.index');
    }

    public function paymentsGroup(Request $request, $trainingGroup, $year)
    {
        if ($request->ajax()) {
            return $this->paymentRepository->filter($request, true);
        }
        $trainingGroup = TrainingGroup::query()->schoolId()->withTrashed()->find($trainingGroup);
        view()->share('year', $year);
        view()->share('trainingGroup', $trainingGroup);
        return view('payments.historic.show');
    }
}
