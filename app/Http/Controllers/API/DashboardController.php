<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\KpiFiltersRequest;
use App\Service\Kpi\KpiDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request) {}

    public function kpis(KpiFiltersRequest $request, KpiDashboardService $kpiDashboardService): JsonResponse
    {
        $response = $kpiDashboardService->resolve(auth()->user(), $request->validated());

        return response()->json($response);
    }
}
