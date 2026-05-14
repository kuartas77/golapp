<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InscriptionCustomChargeUpdateRequest;
use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use Illuminate\Http\JsonResponse;

class InscriptionCustomChargeController extends Controller
{
    public function index(): JsonResponse
    {
        $currentYear = now()->year;

        $charges = InscriptionCustomCharge::query()
            ->with(['inscription.player', 'invoiceCustomItem', 'invoiceItem.invoice'])
            ->schoolId()
            ->where(function ($query) use ($currentYear): void {
                $query->whereHas('inscription', fn ($query) => $query->where('year', $currentYear))
                    ->orWhere(function ($query) use ($currentYear): void {
                        $query->where('status', InscriptionCustomCharge::STATUS_DUE)
                            ->whereHas('inscription', fn ($query) => $query->where('year', '<', $currentYear));
                    });
            })
            ->latest('id')
            ->get();

        return response()->json($charges);
    }

    public function byInscription(Inscription $inscription): JsonResponse
    {
        abort_unless((int) $inscription->school_id === (int) getSchool(auth()->user())->id, 404);

        $charges = $inscription->customCharges()
            ->with(['invoiceCustomItem', 'invoiceItem.invoice'])
            ->latest('id')
            ->get();

        return response()->json($charges);
    }

    public function update(
        InscriptionCustomChargeUpdateRequest $request,
        InscriptionCustomCharge $charge
    ): JsonResponse {
        abort_unless((int) $charge->school_id === (int) getSchool(auth()->user())->id, 404);

        if ($charge->status === InscriptionCustomCharge::STATUS_PAID) {
            return response()->json([
                'message' => 'Los cargos pagados no se pueden modificar.',
            ], 422);
        }

        $charge->update($request->validated());

        return response()->json([
            'message' => 'Cargo personalizado actualizado correctamente.',
            'data' => $charge->fresh(['inscription.player', 'invoiceCustomItem', 'invoiceItem.invoice']),
        ]);
    }

    public function destroy(InscriptionCustomCharge $charge): JsonResponse
    {
        abort_unless((int) $charge->school_id === (int) getSchool(auth()->user())->id, 404);

        if ($charge->status === InscriptionCustomCharge::STATUS_PAID || ! is_null($charge->invoice_item_id)) {
            return response()->json([
                'message' => 'No se puede eliminar un cargo pagado o ya enlazado a una factura.',
            ], 422);
        }

        $charge->delete();

        return response()->json([
            'message' => 'Cargo personalizado eliminado correctamente.',
        ]);
    }
}
