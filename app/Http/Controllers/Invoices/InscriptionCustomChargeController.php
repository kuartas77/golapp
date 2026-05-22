<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\InscriptionCustomCharge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InscriptionCustomChargeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()->of($this->query())
                ->filterColumn('status', fn ($query, $keyword) => $query->where('inscription_custom_charges.status', $keyword))
                ->filterColumn('due_date', fn ($query, $keyword) => $query->whereDate('inscription_custom_charges.due_date', $keyword))
                ->filterColumn('player.full_names', function($query, $keyword) {
                    $sql = "CONCAT(players.names, ' ', players.last_names) like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('inscription.unique_code', function($query, $keyword) {
                    $query->whereRaw('players.unique_code like ?', ["%{$keyword}%"]);
                })
                ->toJson();
        }

        return view('invoices.inscription-custom-charges', [
            'statuses' => InscriptionCustomCharge::statuses(),
        ]);
    }

    public function update(Request $request, InscriptionCustomCharge $charge): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);
        abort_unless($charge->school_id == getSchool(auth()->user())->id, 404);

        $data = $request->validate([
            'value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(InscriptionCustomCharge::statuses()))],
            'due_date' => ['required', 'date'],
        ]);

        $charge->update($data);

        return response()->json([
            'message' => 'Cargo actualizado correctamente.',
            'data' => $charge->fresh(),
        ]);
    }

    public function destroy(InscriptionCustomCharge $charge): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);
        abort_unless($charge->school_id == getSchool(auth()->user())->id, 404);

        abort_if(
            $charge->invoice_item_id !== null
            || !in_array($charge->status, [InscriptionCustomCharge::STATUS_PENDING, InscriptionCustomCharge::STATUS_DUE], true),
            422,
            'Sólo se pueden eliminar cargos pendientes o en debe que no estén facturados.'
        );

        $charge->delete();

        return response()->json([
            'message' => 'Cargo eliminado correctamente.',
        ]);
    }

    private function query()
    {
        return InscriptionCustomCharge::query()
            ->select('inscription_custom_charges.*')
            ->with(['inscription', 'player', 'invoiceCustomItem', 'invoiceItem.invoice'])
            ->join('players', 'players.id', 'player_id')
            ->where('inscription_custom_charges.school_id', getSchool(auth()->user())->id)
            // ->whereIn('status', [InscriptionCustomCharge::STATUS_DUE, InscriptionCustomCharge::STATUS_PENDING])
            ->where(function ($query) {
                $query
                    ->whereHas('inscription', fn ($inscriptionQuery) => $inscriptionQuery->where('year', now()->year))
                    ->orWhere(function ($dueQuery) {
                        $dueQuery
                            ->where('status', InscriptionCustomCharge::STATUS_DUE)
                            ->whereHas('inscription', fn ($inscriptionQuery) => $inscriptionQuery->where('year', '<', now()->year));
                    });
            })
            ->latest('inscription_custom_charges.id');
    }
}
