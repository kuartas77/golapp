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
        $schoolId = getSchool(auth()->user())->id;

        $query = InscriptionCustomCharge::query()
            ->select('inscription_custom_charges.*')
            ->with(['inscription.player', 'invoiceCustomItem', 'invoiceItem.invoice'])
            ->leftJoin('inscriptions', 'inscriptions.id', '=', 'inscription_custom_charges.inscription_id')
            ->leftJoin('players', 'players.id', '=', 'inscriptions.player_id')
            ->leftJoin('invoice_items', 'invoice_items.id', '=', 'inscription_custom_charges.invoice_item_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('inscription_custom_charges.school_id', $schoolId)
            ->where(function ($query) use ($currentYear): void {
                $query->where('inscriptions.year', $currentYear)
                    ->orWhere(function ($query) use ($currentYear): void {
                        $query->where('inscription_custom_charges.status', InscriptionCustomCharge::STATUS_DUE)
                            ->where('inscriptions.year', '<', $currentYear);
                    });
            })
            ->latest('inscription_custom_charges.id');

        return datatables()->eloquent($query)
            ->filterColumn('player_name', function ($query, $keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('players.names', 'like', "%{$keyword}%")
                        ->orWhere('players.last_names', 'like', "%{$keyword}%")
                        ->orWhere('players.unique_code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('invoice_number', fn ($query, $keyword) => $query->where('invoices.invoice_number', 'like', "%{$keyword}%"))
            ->filterColumn('name', fn ($query, $keyword) => $query->where('inscription_custom_charges.name', 'like', "%{$keyword}%"))
            ->orderColumn('player_name', function ($query, $order): void {
                $query->orderBy('players.last_names', $order)
                    ->orderBy('players.names', $order);
            })
            ->orderColumn('invoice_number', 'invoices.invoice_number $1')
            ->toJson();
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
