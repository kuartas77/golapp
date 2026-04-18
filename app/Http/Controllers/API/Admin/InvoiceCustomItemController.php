<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceCustomItemRequest;
use App\Models\InvoiceCustomItem;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class InvoiceCustomItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = InvoiceCustomItem::query()
            ->schoolId()
            ->latest('created_at')
            ->get([
                'id',
                'type',
                'name',
                'unit_price',
                'created_at',
            ]);

        return response()->json($items);
    }

    public function store(InvoiceCustomItemRequest $request): JsonResponse
    {
        return $this->persistItem(
            new InvoiceCustomItem(),
            $request,
            'Item personalizado creado correctamente.',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->findSchoolItem($id));
    }

    public function update(int $id, InvoiceCustomItemRequest $request): JsonResponse
    {
        return $this->persistItem(
            $this->findSchoolItem($id),
            $request,
            'Item personalizado actualizado correctamente.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->findSchoolItem($id)->delete();

        return response()->noContent();
    }

    private function persistItem(
        InvoiceCustomItem $invoiceCustomItem,
        InvoiceCustomItemRequest $request,
        string $message,
        int $status = 200
    ): JsonResponse {
        try {
            $invoiceCustomItem->fill($request->validated());
            $invoiceCustomItem->school_id = getSchool(auth()->user())->id;
            $invoiceCustomItem->save();

            return response()->json([
                'message' => $message,
                'data' => $invoiceCustomItem->fresh(),
            ], $status);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                $message = 'Un item de este tipo ya se encuentra registrado.';

                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'type' => [$message],
                    ],
                ], 422);
            }

            report($exception);

            return response()->json([
                'message' => 'No fue posible guardar el item personalizado.',
            ], 500);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible guardar el item personalizado.',
            ], 500);
        }
    }

    private function findSchoolItem(int $id): InvoiceCustomItem
    {
        return InvoiceCustomItem::query()
            ->schoolId()
            ->findOrFail($id);
    }
}
