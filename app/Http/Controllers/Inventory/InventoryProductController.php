<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryMovementRequest;
use App\Http\Requests\InventoryProductRequest;
use App\Models\InventoryProduct;
use App\Service\Inventory\InventoryMovementService;
use Illuminate\Http\JsonResponse;

class InventoryProductController extends Controller
{
    public function __construct(private InventoryMovementService $movementService) {}

    public function index(): JsonResponse
    {
        $products = InventoryProduct::query()
            ->schoolId()
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $products]);
    }

    public function store(InventoryProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $initialStock = (int) ($data['stock_quantity'] ?? 0);
        $data['stock_quantity'] = 0;

        $product = InventoryProduct::query()->create($data + [
            'school_id' => getSchool(auth()->user())->id,
        ]);

        if ($initialStock > 0) {
            $this->movementService->record($product, [
                'type' => 'adjustment',
                'quantity' => $initialStock,
                'entry_price_snapshot' => $product->entry_price,
                'sale_price_snapshot' => $product->unit_price,
                'price_snapshot' => $product->unit_price,
                'reason' => 'Stock inicial',
                'notes' => null,
                'movement_date' => now()->toDateString(),
            ]);
        }

        return response()->json([
            'message' => 'Producto de inventario creado correctamente.',
            'data' => $product->fresh(),
        ], 201);
    }

    public function show(InventoryProduct $product): JsonResponse
    {
        $this->authorizeSchoolProduct($product);

        return response()->json([
            'data' => $product->load(['movements' => fn ($query) => $query->latest('movement_date')->latest('id')->limit(10)]),
        ]);
    }

    public function update(InventoryProductRequest $request, InventoryProduct $product): JsonResponse
    {
        $this->authorizeSchoolProduct($product);

        $data = $request->validated();
        unset($data['stock_quantity']);

        $product->fill($data);
        $product->save();

        return response()->json([
            'message' => 'Producto de inventario actualizado correctamente.',
            'data' => $product->fresh(),
        ]);
    }

    public function movement(InventoryMovementRequest $request, InventoryProduct $product): JsonResponse
    {
        $this->authorizeSchoolProduct($product);

        $movement = $this->movementService->record($product, $request->validated());

        return response()->json([
            'message' => 'Movimiento de inventario registrado correctamente.',
            'data' => $movement->load(['product', 'user']),
        ], 201);
    }

    private function authorizeSchoolProduct(InventoryProduct $product): void
    {
        abort_unless((int) $product->school_id === (int) getSchool(auth()->user())->id, 404);
    }
}
