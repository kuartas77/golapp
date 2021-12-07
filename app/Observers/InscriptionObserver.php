<?php

namespace App\Observers;

use App\Models\Inscription;
use Jenssegers\Date\Date;

class InscriptionObserver
{
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
        $start_date = Date::parse($inscription->start_date);
        $inscription->payments()->withTrashed()->updateOrCreate(
            [
                'unique_code' => $inscription->unique_code,
                'year' => $start_date->year,
                'school_id' => $inscription->school_id
            ],
            [
                'training_group_id' => $inscription->training_group_id,
                'deleted_at' => null,
                'school_id' => $inscription->school_id
            ]);

        $assistance = [
            'training_group_id' => $inscription->training_group_id,
            'year' => $start_date->year,
            'month' => getMonth($start_date->month),
            'school_id' => $inscription->school_id
        ];

        $inscription->assistance()->withTrashed()->updateOrCreate($assistance, $assistance);
    }
}
