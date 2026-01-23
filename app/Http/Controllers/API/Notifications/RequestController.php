<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestController extends Controller
{
    public function statistics(Request $request): JsonResponse
    {
        $response = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'delivered' => 0,
            'rejected' => 0,
            'cancelled' => 0,
        ];

        return response()->json($response, 200);
    }

    public function index(Request $request): JsonResponse
    {
        $response = [
            'id' => '',
            'user_id' => '',
            'user_name' => null,
            'type' => 'UNIFORM',
            'quantity' => 0,
            'size' => null,
            'brand' => null,
            'model' => null,
            'color' => null,
            'additional_notes' => null,
            'status' => 'PENDING',
            'created_at' => null,
            'updated_at' => null,
            'approved_at' => null,
            'delivered_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];

        return response()->json([$response], 200);
    }

    public function store(Request $request)
    {
        $response = [
            'id' => Str::uuid(),
            'user_id' => '',
            'user_name' => null,
            'type' => 'UNIFORM',
            'quantity' => 0,
            'size' => null,
            'brand' => null,
            'model' => null,
            'color' => null,
            'additional_notes' => null,
            'status' => 'PENDING',
            'created_at' => null,
            'updated_at' => null,
            'approved_at' => null,
            'delivered_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
        return response()->json($response, 200);
    }
}
