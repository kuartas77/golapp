<?php

namespace App\Service;

use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Traits\ErrorTrait;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Throwable;

class SharedService
{
    use ErrorTrait;

    private array $searchPayment;
    private array $searchAssist;
    private array $dataPayment;
    private array $dataAssist;

    public function paymentAssist(Inscription $inscription)
    {
        try {
            DB::beginTransaction();

            $start_date = Date::parse($inscription->start_date);
            if($inscription->wasRecentlyCreated){

                if (!$inscription->training_group_id) {
                    $trainingGroup = TrainingGroup::orderBy('id', 'asc')->firstWhere('school_id', $inscription->school_id);
                    $inscription->training_group_id = $trainingGroup->id;
                    $inscription->save();
                }

                $paymentValue = $inscription->scholarship ? '8': '0';

                $dataPayment = [
                    'inscription_id' => $inscription->id,
                    'year' => $start_date->year,
                    'training_group_id' => $inscription->training_group_id,
                    'school_id' => $inscription->school_id,
                    'unique_code' => $inscription->unique_code,
                    'january' => $paymentValue,
                    'february' => $paymentValue,
                    'march' => $paymentValue,
                    'april' => $paymentValue,
                    'may' => $paymentValue,
                    'june' => $paymentValue,
                    'july' => $paymentValue,
                    'august' => $paymentValue,
                    'september' => $paymentValue,
                    'october' => $paymentValue,
                    'november' => $paymentValue,
                    'december' => $paymentValue,
                ];

                if ($start_date->month > 1) {
                    $this->checkMonthValue($start_date->month, $paymentValue, $dataPayment);
                }

                $assistance = [
                    'training_group_id' => $inscription->training_group_id,
                    'year' => $start_date->year,
                    'month' => $start_date->month,
                    'school_id' => $inscription->school_id
                ];

                $inscription->payments()->create($dataPayment);

                $inscription->assistance()->create($assistance);

            }else{
                if($inscription->isDirty('training_group_id')){

                    $dataToUpdate = ['training_group_id' => $inscription->training_group_id, 'deleted_at' => null];

                    $inscription->payments()->withTrashed()->where('year', $start_date->year)->update($dataToUpdate);

                    $inscription->assistance()->withTrashed()->where('year', $start_date->year)->update($dataToUpdate);

                    $this->enableSkillControl($inscription);
                }
            }

            DB::commit();

        } catch (Throwable $th) {
            DB::rollBack(2);
            $this->logError(__METHOD__, $th);
        }

    }


    private function checkMonthValue(int $actualMonth, $value, &$dataPayment)
    {
        $configMonths = config('variables.KEY_INDEX_MONTHS');
        foreach (range(1, $actualMonth) as $numMonth) {
            $dataPayment[$configMonths[$numMonth]] = ($actualMonth == $numMonth) ? $value : '14'; //No aplica
        }
    }

    private function enableSkillControl($inscription)
    {
        $inscription->skillsControls()->withTrashed()->restore();
    }

    /**
     * @param $inscription_id
     * @param $request
     * @return bool
     */
    public function assignTrainingGroup($inscription_id, $request): bool
    {
        try {
            $origin_group = $request->input('origin_group', null);
            $target_group = $request->input('target_group', null);
            $inscription = Inscription::query()->findOrFail($inscription_id);

            if (!is_null($target_group)) {

                DB::beginTransaction();

                $state = $inscription->update(['training_group_id' => $target_group]);

                DB::commit();

                return $state;
            }

            return false;

        } catch (Throwable $th) {
            DB::rollBack();
            $this->logError(__METHOD__, $th);
            return false;
        }
    }
}
