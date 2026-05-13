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
                ->toJson();
        }

        return view('invoices.inscription-custom-charges', [
            'statuses' => InscriptionCustomCharge::statuses(),
        ]);
    }

    public function update(Request $request, InscriptionCustomCharge $charge): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);
        abort_unless($charge->school_id === getSchool(auth()->user())->id, 404);

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

    private function query()
    {
        return InscriptionCustomCharge::query()
            ->select('inscription_custom_charges.*')
            ->with(['inscription', 'player', 'invoiceCustomItem', 'invoiceItem.invoice'])
            ->schoolId()
            ->latest('inscription_custom_charges.id');
    }
}
