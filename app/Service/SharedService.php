<?php

namespace App\Service;

use Jenssegers\Date\Date;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;

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
            $year = $start_date->year;
            $month = getMonth($start_date->month);

            $this->searchPayment = [
                'inscription_id' => $inscription->id,
                'year' => $year,
                'school_id' => $inscription->school_id
            ];
            
            $this->searchAssist = [
                'inscription_id' => $inscription->id,
                'year' => $year,
                'month' => $month,
            ];

            $this->dataPayment = [
                'inscription_id' => $inscription->id, 
                'year' => $year,
                'training_group_id' => $inscription->training_group_id,
                'unique_code' => $inscription->unique_code,
                'deleted_at' => null,
                'school_id' => $inscription->school_id
            ];

            $this->dataAssist = [
                'training_group_id' => $inscription->training_group_id,
                'year' => $year,
                'month' => $month,
                'deleted_at' => null
            ];

            if($inscription->wasRecentlyCreated){
                $value = $inscription->scholarship ? '8': '0';
                $this->dataPayment['january'] = $value;
                $this->dataPayment['february'] = $value;
                $this->dataPayment['march'] = $value;
                $this->dataPayment['april'] = $value;
                $this->dataPayment['may'] = $value;
                $this->dataPayment['june'] = $value;
                $this->dataPayment['july'] = $value;
                $this->dataPayment['august'] = $value;
                $this->dataPayment['september'] = $value;
                $this->dataPayment['october'] = $value;
                $this->dataPayment['november'] = $value;
                $this->dataPayment['december'] = $value;
            }

            $this->createOrUpdatePaymentAssist($inscription);

            if(!$inscription->training_group_id){
                $trainingGroup = TrainingGroup::orderBy('id','asc')->firstWhere('school_id', $inscription->school_id);
                $inscription->training_group_id = $trainingGroup->id;
                $inscription->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack(2);
            $this->logError(__METHOD__, $th);
        }
        
    }

    /**
     * @param $inscription_id
     * @param $request
     * @return bool
     */
    public function assignTrainingGroup($inscription_id, $request): bool
    {
        $origin_group = $request->input('origin_group', null);
        $target_group = $request->input('target_group', null);
        $inscription = Inscription::query()->find($inscription_id);
        if (is_null($target_group) || empty($inscription)) {
            return false;
        }

        $date = now();
        $year = $date->year;
        $month = getMonth($date->month);

        try {
            DB::beginTransaction();

            $this->searchPayment = [
                'inscription_id' => $inscription->id,
                'year' => $year,
                'school_id' => $inscription->school_id
            ];
            
            $this->searchAssist = [
                'inscription_id' => $inscription->id,
                'year' => $year,
                'month' => $month,
            ];

            $this->dataPayment = [
                'inscription_id' => $inscription->id, 
                'year' => $year,
                'training_group_id' => $inscription->training_group_id,
                'unique_code' => $inscription->unique_code,
                'deleted_at' => null,
                'school_id' => $inscription->school_id
            ];

            $this->dataAssist = [
                'training_group_id' => $inscription->training_group_id,
                'year' => $year,
                'month' => $month,
                'deleted_at' => null
            ];
            
            $this->createOrUpdatePaymentAssist($inscription);
            
            $state = $inscription->update(['training_group_id' => $target_group]);

            DB::commit();

            return $state;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError(__METHOD__, $th);
            return false;
        }
    }

    private function createOrUpdatePaymentAssist($inscription)
    {
        $inscription->payments()->updateOrCreate(
            $this->searchPayment,
            $this->dataPayment
        );

        $inscription->assistance()->updateOrCreate(
            $this->searchAssist,
            $this->dataAssist
        );
    }
}
