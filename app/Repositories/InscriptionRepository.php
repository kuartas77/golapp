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
     * @param FormRequest $request
     * @param bool $created
     * @param Inscription|null $inscription
     * @return Inscription|null
     */
    public function setInscription(FormRequest $request, bool $created = true, Inscription $inscription = null): Inscription
    {
        try {
            DB::beginTransaction();

            $inscriptionData = $request->only($this->model->getFillable());
            $inscriptionData['training_group_id'] = request('training_group_id', TrainingGroup::orderBy('id', 'asc')->firstWhere('school_id', getSchool(auth()->user())->id)->id);
            $inscriptionData['deleted_at'] = null;

            if ($created) {
                $inscription = $this->model->withTrashed()->updateOrCreate([
                    'unique_code' => $inscriptionData['unique_code'],
                    'year' => $inscriptionData['year']
                ], $inscriptionData);

                $inscription->load(['player', 'school']);

                $inscription->player->notify(new InscriptionNotification($inscription));
            } else {
                $inscriptionData['unique_code'] = $inscription->unique_code;
                $inscriptionData['start_date'] = $inscription->start_date;
                $inscription->update($inscriptionData);
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
            $inscriptions->setAppends(['url_edit', 'url_update', 'url_show', 'url_impression'/*, 'url_destroy'*/]);
        }
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
            $inscription->delete();
            DB::commit();
            alert()->success(env('APP_NAME'), __('messages.ins_delete_success'));
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError("InscriptionRepository disable", $th);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }

}
