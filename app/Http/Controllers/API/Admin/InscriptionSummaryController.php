<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Service\Inscription\InscriptionSummaryService;
use Illuminate\Http\JsonResponse;

class InscriptionSummaryController extends Controller
{
    public function __construct(private InscriptionSummaryService $summaryService) {}

    public function show(Inscription $inscription): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);
        abort_if((int) $inscription->school_id !== (int) getSchool(auth()->user())->id, 404);

        return response()->json([
            'data' => $this->summaryService->payload($inscription),
        ]);
    }
}
