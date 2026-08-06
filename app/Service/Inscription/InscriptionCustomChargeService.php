<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\InvoiceCustomItem;

class InscriptionCustomChargeService
{
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
