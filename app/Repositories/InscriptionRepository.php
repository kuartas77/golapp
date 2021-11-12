<?php

namespace App\Repositories;

use Exception;
use Jenssegers\Date\Date;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use Illuminate\Http\Request;
use App\Events\InscriptionAdded;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class InscriptionRepository
{
    use ErrorTrait;

    /**
     * @var Inscription
     */
    private Inscription $model;
    /**
     * @var PeopleRepository
     */
    private PeopleRepository $peopleRepository;

    /**
     *
     * @param Inscription $model
     * @param PeopleRepository $peopleRepository
     */


    public function __construct(Inscription $model, PeopleRepository $peopleRepository)
    {
        $this->model = $model;
        $this->peopleRepository = $peopleRepository;
    }

    /**
     * @param $id
     * @param false $trashed
     * @return mixed
     */
    public function findById($id, bool $trashed = false)
    {
        if ($trashed) {
            return Inscription::onlyTrashed()->findOrFail($id);
        }
        return Inscription::query()->findOrFail($id);

    }

    /**
     * @param Request $request
     * @param bool $created
     * @param Inscription|null $inscription
     * @return Inscription|null
     */
    public function setInscription(Request $request,bool $created = true, Inscription $inscription = null): Inscription
    {
        try {
            DB::beginTransaction();

            $inscriptionData = $request->only($this->model->getFillable());
            $inscriptionData['training_group_id'] = request('training_group_id', 1);
            $inscriptionData['deleted_at'] = null;

            if ($created){
                $inscription = $this->model->withTrashed()->updateOrCreate([
                    'unique_code' => $inscriptionData['unique_code'],
                    'year' => $inscriptionData['year']
                ],$inscriptionData);
            }else{
                $inscription->fill($inscriptionData)->save();
            }

            DB::commit();
            return $inscription;

        } catch (Exception $exception) {
            DB::rollBack();
            $method = $created ? "created" : "update";
            $this->logError("InscriptionRepository {$method}", $exception);
            return $this->model;
        }
    }


    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsEnabled()
    {
       $inscriptions = $this->model->with(['player.peoples', 'trainingGroup.schedule.day'])
            ->where('year', now())->get();
       if ($inscriptions->isNotEmpty()){
           $inscriptions->setAppends(['url_edit','url_update','url_show', 'url_impression']);
       }
       return $inscriptions;
    }

    public function searchInscriptionCompetition(array $fields)
    {
        return $this->model->query()->where('unique_code', $fields['unique_code'])
            ->where('competition_group_id', '!=', $fields['competition_group_id'])
            ->where('year', now())
            ->first();
    }

    public function searchInsUniqueCode($id)
    {
        return $this->model->with('player')->findOrFail($id);
    }

}
