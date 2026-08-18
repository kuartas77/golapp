<?php

declare(strict_types=1);

namespace App\Service\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceNumberRange;
use App\Models\School;
use App\Models\SchoolInvoiceSequence;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceNumberAllocator
{
    /** @return array{invoice_number:string, numbering_type:string, consecutive_number:int, invoice_number_range_id:int|null} */
    public function allocate(int $schoolId, CarbonInterface $issueDate): array
    {
        $school = School::query()->lockForUpdate()->findOrFail($schoolId);

        return $school->electronic_invoicing_enabled
            ? $this->allocateElectronic($schoolId, $issueDate)
            : $this->allocateInternal($schoolId);
    }

    private function allocateInternal(int $schoolId): array
    {
        DB::table('school_invoice_sequences')->insertOrIgnore([
            'school_id' => $schoolId,
            'next_number' => Invoice::withTrashed()->where('school_id', $schoolId)->count() + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = SchoolInvoiceSequence::query()->where('school_id', $schoolId)->lockForUpdate()->firstOrFail();
        $number = $sequence->next_number;
        $sequence->increment('next_number');

        return [
            'invoice_number' => 'FAC-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT),
            'numbering_type' => 'internal',
            'consecutive_number' => $number,
            'invoice_number_range_id' => null,
        ];
    }

    private function allocateElectronic(int $schoolId, CarbonInterface $issueDate): array
    {
        $range = InvoiceNumberRange::query()->where('school_id', $schoolId)
            ->where('active_slot', 1)->lockForUpdate()->first();

        if (! $range
            || $issueDate->toDateString() < $range->valid_from->toDateString()
            || $issueDate->toDateString() > $range->valid_until->toDateString()
            || $range->next_number > $range->range_end) {
            throw ValidationException::withMessages([
                'invoice_number' => ['La escuela no tiene una resolución de facturación activa, vigente y con números disponibles.'],
            ]);
        }

        $number = $range->next_number;
        $range->forceFill(['next_number' => $number + 1, 'used_at' => $range->used_at ?? now()])->save();

        return [
            'invoice_number' => ($range->prefix ?? '').$number,
            'numbering_type' => 'electronic',
            'consecutive_number' => $number,
            'invoice_number_range_id' => $range->id,
        ];
    }
}
