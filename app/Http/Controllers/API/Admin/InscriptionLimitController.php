<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Service\InscriptionLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscriptionLimitController extends Controller
{
    public function __invoke(Request $request, InscriptionLimitService $inscriptionLimitService): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        return response()->json($inscriptionLimitService->summary(
            getSchool($request->user()),
            (int) ($validated['year'] ?? now()->year)
        ));
    }
}
