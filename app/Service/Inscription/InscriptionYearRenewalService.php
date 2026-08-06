<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Service\Groups\GroupCatalogCache;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class InscriptionYearRenewalService
{
    public function __construct(private Inscription $inscription) {}

    public function createByYear(
        int|string|null $actualYear = null,
        int|string|Carbon|null $futureYear = null
    ): void {
        try {
            $actualYear = (int) ($actualYear ?: now()->year);

            if ($futureYear instanceof Carbon) {
                $futureYearValue = (int) $futureYear->year;
                $futureStartDate = $futureYear->copy()->startOfYear()->format('Y-m-d');
            } elseif (is_numeric($futureYear)) {
                $futureYearValue = (int) $futureYear;
                $futureStartDate = Carbon::create($futureYearValue, 1, 1)->format('Y-m-d');
            } else {
                $futureDate = now()->addYear()->startOfYear();
                $futureYearValue = (int) $futureDate->year;
                $futureStartDate = $futureDate->format('Y-m-d');
            }

            $trainingGroup = TrainingGroup::query()
                ->orderBy('id')
                ->where('is_complementary', false)
                ->schoolId()
                ->first();
            throw_if(is_null($trainingGroup), Exception::class, 'Training group not found');

            $inscriptions = $this->inscription->where('year', $actualYear)->schoolId()->get();

            DB::beginTransaction();

            foreach ($inscriptions as $inscription) {
                $inscriptionData = [
                    'school_id' => $inscription->school_id,
                    'player_id' => $inscription->player_id,
                    'unique_code' => $inscription->unique_code,
                    'year' => $futureYearValue,
                    'start_date' => $futureStartDate,
                    'category' => $inscription->category,
                    'photos' => $inscription->photos,
                    'copy_identification_document' => $inscription->copy_identification_document,
                    'eps_certificate' => $inscription->eps_certificate,
                    'medic_certificate' => $inscription->medic_certificate,
                    'study_certificate' => $inscription->study_certificate,
                    'overalls' => $inscription->overalls,
                    'ball' => $inscription->ball,
                    'bag' => $inscription->bag,
                    'presentation_uniform' => $inscription->presentation_uniform,
                    'competition_uniform' => $inscription->competition_uniform,
                    'tournament_pay' => $inscription->tournament_pay,
                    'period_one' => $inscription->period_one,
                    'period_two' => $inscription->period_two,
                    'period_three' => $inscription->period_three,
                    'period_four' => $inscription->period_four,
                    'scholarship' => $inscription->scholarship,
                    'brother_payment' => $inscription->brother_payment,
                    'monthly_payment_type' => $inscription->monthly_payment_type,
                    'monthly_payment_amount' => $inscription->monthly_payment_amount,
                    'training_group_id' => $trainingGroup->id,
                ];

                $this->inscription->withTrashed()->updateOrCreate([
                    'unique_code' => $inscriptionData['unique_code'],
                    'year' => $inscriptionData['year'],
                    'school_id' => $inscriptionData['school_id'],
                ], $inscriptionData);
            }

            DB::commit();
            app(GroupCatalogCache::class)->invalidateSchool((int) $trainingGroup->school_id);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
        }
    }
}
