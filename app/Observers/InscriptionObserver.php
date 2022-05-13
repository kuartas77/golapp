<?php

namespace App\Observers;

use App\Models\Inscription;
use App\Traits\ErrorTrait;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;

class InscriptionObserver
{
    use ErrorTrait;
    /**
     * Handle the inscription "created" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function created(Inscription $inscription)
    {
        $this->paymentAssist($inscription);
    }

    /**
     * Handle the inscription "updated" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function updated(Inscription $inscription)
    {
        $this->paymentAssist($inscription);
    }

    /**
     * Handle the inscription "deleted" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function deleted(Inscription $inscription)
    {
        $inscription->payments()->delete();
        $inscription->assistance()->delete();
    }

    /**
     * Handle the inscription "restored" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function restored(Inscription $inscription)
    {

    }

    /**
     * Handle the inscription "force deleted" event.
     *
     * @param Inscription $inscription
     * @return void
     */
    public function forceDeleted(Inscription $inscription)
    {
        //
    }

    private function paymentAssist($inscription)
    {
        try {
            DB::beginTransaction();

            $start_date = Date::parse($inscription->start_date);

            $dataPayment = [
                'inscription_id' => $inscription->id, 
                'year' => $start_date->year,
                'training_group_id' => $inscription->training_group_id,
                'unique_code' => $inscription->unique_code,
                'deleted_at' => null,
                'school_id' => $inscription->school_id
            ];

            if($inscription->wasRecentlyCreated){
                $value = $inscription->scholarship ? '8': '0';
                $dataPayment['january'] = $value;
                $dataPayment['february'] = $value;
                $dataPayment['march'] = $value;
                $dataPayment['april'] = $value;
                $dataPayment['may'] = $value;
                $dataPayment['june'] = $value;
                $dataPayment['july'] = $value;
                $dataPayment['august'] = $value;
                $dataPayment['september'] = $value;
                $dataPayment['october'] = $value;
                $dataPayment['november'] = $value;
                $dataPayment['december'] = $value;
            }

            $inscription->payments()->withTrashed()->updateOrCreate(
                [
                    'inscription_id' => $inscription->id, 
                    'year' => $start_date->year,
                    'training_group_id' => $inscription->training_group_id,
                    'unique_code' => $inscription->unique_code,
                ], $dataPayment
            );

            $assistance = [
                'training_group_id' => $inscription->training_group_id,
                'year' => $start_date->year,
                'month' => getMonth($start_date->month),
                'deleted_at' => null
            ];

            $inscription->assistance()->withTrashed()->updateOrCreate(
                [
                    'inscription_id' => $inscription->id, 
                    'training_group_id' => $inscription->training_group_id,
                    'year' => $start_date->year,
                    'month' => getMonth($start_date->month)
                ], $assistance);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack(2);
            $this->logError("InscriptionObserver", $th);
        }
        
    }
}
