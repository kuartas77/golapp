<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectronicInvoicingModeRequest;
use App\Http\Requests\InvoiceNumberRangeRequest;
use App\Models\InvoiceNumberRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceNumberRangeController extends Controller
{
    public function index(): JsonResponse
    {
        $school = getSchool(auth()->user());

        return response()->json([
            'electronic_invoicing_enabled' => (bool) $school->electronic_invoicing_enabled,
            'can_toggle_electronic_invoicing' => isAdmin(),
            'ranges' => InvoiceNumberRange::query()->where('school_id', $school->id)->latest()->get(),
        ]);
    }

    public function store(InvoiceNumberRangeRequest $request): JsonResponse
    {
        $schoolId = (int) getSchool(auth()->user())->id;
        $data = $request->validated();
        $this->ensureNoOverlap($schoolId, $data);

        $range = InvoiceNumberRange::query()->create($data + ['school_id' => $schoolId]);

        return response()->json(['message' => 'Resolución registrada correctamente.', 'data' => $range], 201);
    }

    public function update(InvoiceNumberRangeRequest $request, int $range): JsonResponse
    {
        $model = $this->findRange($range);
        $data = $request->validated();

        if ($model->used_at) {
            $technicalKey = $data['technical_key'] ?? null;
            if (filled($technicalKey)) {
                $model->forceFill(['technical_key' => $technicalKey])->save();
            }

            return response()->json([
                'message' => 'La resolución ya fue utilizada; sólo se actualizó su clave técnica.',
                'data' => $model->fresh(),
            ]);
        }

        $this->ensureNoOverlap((int) $model->school_id, $data, $model->id);
        if ($model->is_active
            && (! today()->betweenIncluded($data['valid_from'], $data['valid_until'])
                || $data['next_number'] > $data['range_end'])) {
            throw ValidationException::withMessages([
                'valid_until' => ['Una resolución activa debe permanecer vigente y con números disponibles.'],
            ]);
        }
        if (blank($data['technical_key'] ?? null)) {
            unset($data['technical_key']);
        }
        $model->fill($data)->save();

        return response()->json(['message' => 'Resolución actualizada correctamente.', 'data' => $model->fresh()]);
    }

    public function destroy(int $range): Response
    {
        $model = $this->findRange($range);
        abort_if($model->used_at || $model->is_active, 422, 'Una resolución activa o utilizada no se puede eliminar.');
        $model->delete();

        return response()->noContent();
    }

    public function activate(int $range): JsonResponse
    {
        $model = $this->findRange($range);
        abort_unless(
            today()->betweenIncluded($model->valid_from, $model->valid_until) && $model->next_number <= $model->range_end,
            422,
            'La resolución debe estar vigente y tener números disponibles.'
        );

        DB::transaction(function () use ($model): void {
            InvoiceNumberRange::query()->where('school_id', $model->school_id)->lockForUpdate()->get();
            InvoiceNumberRange::query()->where('school_id', $model->school_id)->update([
                'is_active' => false,
                'active_slot' => null,
            ]);
            $model->forceFill(['is_active' => true, 'active_slot' => 1])->save();
        });

        return response()->json(['message' => 'Resolución activada correctamente.', 'data' => $model->fresh()]);
    }

    public function deactivate(int $range): JsonResponse
    {
        $model = $this->findRange($range);
        $model->forceFill(['is_active' => false, 'active_slot' => null])->save();

        return response()->json(['message' => 'Resolución desactivada correctamente.', 'data' => $model->fresh()]);
    }

    public function updateElectronicMode(ElectronicInvoicingModeRequest $request): JsonResponse
    {
        $school = getSchool(auth()->user());
        $enabled = (bool) $request->validated('enabled');

        if ($enabled) {
            $usable = InvoiceNumberRange::query()->where('school_id', $school->id)
                ->where('active_slot', 1)
                ->whereDate('valid_from', '<=', today())
                ->whereDate('valid_until', '>=', today())
                ->whereColumn('next_number', '<=', 'range_end')
                ->exists();
            throw_unless($usable, ValidationException::withMessages([
                'enabled' => ['Debes activar una resolución vigente y con números disponibles.'],
            ]));
        }

        $school->forceFill(['electronic_invoicing_enabled' => $enabled])->save();

        return response()->json(['electronic_invoicing_enabled' => $enabled]);
    }

    private function findRange(int $id): InvoiceNumberRange
    {
        return InvoiceNumberRange::query()->where('school_id', getSchool(auth()->user())->id)->findOrFail($id);
    }

    private function ensureNoOverlap(int $schoolId, array $data, ?int $ignoreId = null): void
    {
        $query = InvoiceNumberRange::query()->where('school_id', $schoolId)
            ->where('prefix', $data['prefix'])
            ->where('range_start', '<=', $data['range_end'])
            ->where('range_end', '>=', $data['range_start']);
        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages([
                'range_start' => ['El rango se cruza con otra resolución del mismo prefijo.'],
            ]);
        }
    }
}
