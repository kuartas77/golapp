<?php

namespace App\Repositories;

use Exception;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Collection;
use App\Notifications\InscriptionNotification;
use Throwable;

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
            return Inscription::onlyTrashed()->schoolId()->findOrFail($id);
        }
        return Inscription::query()->schoolId()->findOrFail($id);

    }

    /**
     * @param array $inscriptionData
     * @param bool $created
     * @param Inscription|null $inscription
     * @return Inscription|null
     */
    public function setInscription(array $inscriptionData, bool $created = true, Inscription $inscription = null): Inscription
    {
        try {
            if(!$inscriptionData['training_group_id']){
                $inscriptionData['training_group_id'] = TrainingGroup::query()->orderBy('id', 'asc')
                ->firstWhere('school_id', $inscriptionData['school_id'])->id;
            }

            $inscriptionData['deleted_at'] = null;

            DB::beginTransaction();

            $competition_groups = [];
            if(isset($inscriptionData['competition_groups']))
            {
                $competition_groups = $inscriptionData['competition_groups'];
                unset($inscriptionData['competition_groups']);
            }

            if ($created) {

                $inscription = $this->model->withTrashed()->updateOrCreate([
                    'unique_code' => $inscriptionData['unique_code'],
                    'year' => $inscriptionData['year']
                ], $inscriptionData);

                $inscription->load(['player', 'school']);
                if($inscription->player->email && filter_var($inscription->player->email, FILTER_VALIDATE_EMAIL)){
                    $inscription->player->notify(new InscriptionNotification($inscription));
                }
            } else {
                $inscriptionData['unique_code'] = $inscription->unique_code;
                $inscriptionData['start_date'] = $inscription->start_date;
                $inscription->update($inscriptionData);
            }

            if(!empty($competition_groups)){
                $inscription->competitionGroup()->sync($competition_groups);
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
        $inscriptions = $this->model->with(['player.people', 'trainingGroup'])
            ->where('year', now()->year)->schoolId()->get();
        if ($inscriptions->isNotEmpty()) {
            $inscriptions->setAppends(['url_edit', 'url_update', 'url_show', 'url_impression', 'url_destroy']);
        }
        return $inscriptions;
    }

    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsDisabled()
    {
        $inscriptions = $this->model->with(['player.people', 'trainingGroup'])
            ->where('year', now()->year)->schoolId()->onlyTrashed()->get();
        // if ($inscriptions->isNotEmpty()) {
        //     $inscriptions->setAppends(['url_edit', 'url_update', 'url_show', 'url_impression', 'url_destroy']);
        // }
        return $inscriptions;
    }

    public function searchInscriptionCompetition(array $fields)
    {
        return $this->model->query()->with('player')
            ->where('unique_code', $fields['unique_code'])
            ->where(function ($query) use ($fields) {
                $query->where('competition_group_id', '<>', $fields['competition_group_id'])
                    ->orWhere('competition_group_id', null);
            })
            ->where('year', now()->year)
            ->first();
    }

    public function searchInsUniqueCode($id)
    {
        return $this->model->query()->with('player')->schoolId()->findOrFail($id);
    }

    public function disable(Inscription $inscription)
    {
        try {
            DB::beginTransaction();
            // $inscription->load(['payments', 'skillsControls', 'assistance']);
            $inscription->payments()->delete();
            $inscription->skillsControls()->delete();
            $inscription->assistance()->delete();
            $inscription->tournament_payouts()->delete();
            $inscription->delete();
            DB::commit();
            alert()->success(env('APP_NAME'), __('messages.ins_delete_success'));
        } catch (Throwable $th) {
            DB::rollBack();
            $this->logError("InscriptionRepository disable", $th);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }

}
