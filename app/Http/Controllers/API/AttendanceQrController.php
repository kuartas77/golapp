<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AttendanceQrTakeRequest;
use App\Service\Assist\AttendanceQrService;
use Illuminate\Http\JsonResponse;

class AttendanceQrController extends Controller
{
    public function __construct(private AttendanceQrService $service) {}

    public function show(string $uniqueCode): JsonResponse
    {
        return response()->json($this->service->context($uniqueCode));
    }

    public function take(AttendanceQrTakeRequest $request, int $assist): JsonResponse
    {
        return response()->json($this->service->take($assist, $request->string('column')->value()));
    }
}
