<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\InvoiceCustomItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InscriptionCustomChargeService
{
    /** @param array<int, array<string, mixed>> $charges */
    public function add(Inscription $inscription, array $charges): array
    {
        return DB::transaction(function () use ($inscription, $charges): array {
            $lockedInscription = Inscription::query()
                ->whereKey($inscription->id)
                ->where('school_id', $inscription->school_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $lockedInscription->year !== (int) now()->year) {
                throw ValidationException::withMessages([
                    'inscription' => ['Sólo se pueden agregar cargos a inscripciones del año actual.'],
                ]);
            }

            $catalogIds = collect($charges)->pluck('invoice_custom_item_id')->map(fn ($id) => (int) $id);
            $catalogItems = InvoiceCustomItem::query()
                ->where('school_id', $lockedInscription->school_id)
                ->whereIn('id', $catalogIds)
                ->get()
                ->keyBy('id');

            $duplicateIds = InscriptionCustomCharge::query()
                ->where('school_id', $lockedInscription->school_id)
                ->where('inscription_id', $lockedInscription->id)
                ->whereIn('invoice_custom_item_id', $catalogIds)
                ->whereIn('status', [InscriptionCustomCharge::STATUS_PENDING, InscriptionCustomCharge::STATUS_DUE])
                ->whereNull('invoice_item_id')
                ->lockForUpdate()
                ->pluck('invoice_custom_item_id');

            if ($duplicateIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'charges' => ['Ya existe un cargo activo para uno de los conceptos seleccionados.'],
                ]);
            }

            return collect($charges)->map(function (array $charge) use ($lockedInscription, $catalogItems) {
                $catalogItem = $catalogItems->get((int) $charge['invoice_custom_item_id']);

                return InscriptionCustomCharge::query()->create([
                    'school_id' => $lockedInscription->school_id,
                    'inscription_id' => $lockedInscription->id,
                    'player_id' => $lockedInscription->player_id,
                    'invoice_custom_item_id' => $catalogItem->id,
                    'name' => $catalogItem->name,
                    'value' => $charge['value'],
                    'status' => InscriptionCustomCharge::STATUS_PENDING,
                    'due_date' => $charge['due_date'],
                ]);
            })->all();
        });
    }

    /** @param array<int, array<string, mixed>> $customCharges */
    public function sync(Inscription $inscription, array $customCharges): void
    {
        if ($customCharges === []) {
            return;
        }

        $schoolId = (int) $inscription->school_id;
        $catalogIds = collect($customCharges)
            ->pluck('invoice_custom_item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $catalogItems = InvoiceCustomItem::query()
            ->schoolId()
            ->whereIn('id', $catalogIds)
            ->get()
            ->keyBy('id');

        foreach ($customCharges as $chargeData) {
            $value = (float) data_get($chargeData, 'value', 0);
            $dueDate = data_get($chargeData, 'due_date');
            $chargeId = data_get($chargeData, 'id');
            $shouldDelete = (bool) data_get($chargeData, '_delete', false);

            if ($chargeId) {
                $charge = InscriptionCustomCharge::query()
                    ->where('school_id', $schoolId)
                    ->where('inscription_id', $inscription->id)
                    ->find($chargeId);

                if (! $charge || $charge->status === InscriptionCustomCharge::STATUS_PAID) {
                    continue;
                }

                if ($shouldDelete) {
                    if (
                        $charge->status === InscriptionCustomCharge::STATUS_PENDING
                        && is_null($charge->invoice_item_id)
                    ) {
                        $charge->delete();
                    }

                    continue;
                }

                $charge->update([
                    'value' => $value,
                    'due_date' => $dueDate,
                ]);

                continue;
            }

            $catalogId = (int) data_get($chargeData, 'invoice_custom_item_id');
            $catalogItem = $catalogItems->get($catalogId);

            if (! $catalogItem) {
                continue;
            }

            $activeExists = InscriptionCustomCharge::query()
                ->where('school_id', $schoolId)
                ->where('inscription_id', $inscription->id)
                ->where('invoice_custom_item_id', $catalogItem->id)
                ->whereIn('status', [
                    InscriptionCustomCharge::STATUS_PENDING,
                    InscriptionCustomCharge::STATUS_DUE,
                ])
                ->whereNull('invoice_item_id')
                ->exists();

            if ($activeExists) {
                continue;
            }

            InscriptionCustomCharge::query()->create([
                'school_id' => $schoolId,
                'inscription_id' => $inscription->id,
                'player_id' => $inscription->player_id,
                'invoice_custom_item_id' => $catalogItem->id,
                'name' => $catalogItem->name,
                'value' => $value,
                'status' => InscriptionCustomCharge::STATUS_PENDING,
                'due_date' => $dueDate,
            ]);
        }
    }
}
