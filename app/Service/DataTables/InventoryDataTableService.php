<?php

namespace App\Service\DataTables;

use App\Models\InventoryMovement;
use App\Models\InventoryProduct;
use Illuminate\Http\JsonResponse;

class InventoryDataTableService
{
    public function products(int $schoolId): JsonResponse
    {
        return datatables()->eloquent(InventoryProduct::query()->where('school_id', $schoolId))
            ->filterColumn('is_active', fn ($query, $keyword) => $query->where('is_active', $keyword))
            ->filterColumn('is_low_stock', function ($query, $keyword) {
                if ($keyword === '1') $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
                if ($keyword === '0') $query->whereColumn('stock_quantity', '>', 'minimum_stock');
            })
            ->filterColumn('category', fn ($query, $keyword) => $query->where('category', 'like', "%{$keyword}%"))
            ->orderColumn('is_low_stock', 'stock_quantity $1')
            ->addColumn('is_low_stock', fn (InventoryProduct $product) => $product->is_low_stock)
            ->toJson();
    }

    public function movements(int $schoolId): JsonResponse
    {
        $query = InventoryMovement::query()->select('inventory_movements.*')
            ->join('inventory_products', 'inventory_products.id', '=', 'inventory_movements.inventory_product_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_movements.user_id')
            ->where('inventory_movements.school_id', $schoolId)->with(['product', 'user']);

        return datatables()->eloquent($query)
            ->filterColumn('movement_date', fn ($query, $keyword) => $query->whereDate('movement_date', $keyword))
            ->filterColumn('type', fn ($query, $keyword) => $query->where('inventory_movements.type', $keyword))
            ->filterColumn('product_name', fn ($query, $keyword) => $query->where('inventory_products.name', 'like', "%{$keyword}%"))
            ->filterColumn('user_name', fn ($query, $keyword) => $query->where('users.name', 'like', "%{$keyword}%"))
            ->orderColumn('product_name', 'inventory_products.name $1')->orderColumn('user_name', 'users.name $1')
            ->addColumn('product_name', fn (InventoryMovement $movement) => $movement->product?->name ?? '')
            ->addColumn('product_sku', fn (InventoryMovement $movement) => $movement->product?->sku ?? '')
            ->addColumn('user_name', fn (InventoryMovement $movement) => $movement->user?->name ?? '')
            ->addColumn('profit_margin', fn (InventoryMovement $movement) => $movement->profit_margin)->toJson();
    }
}
