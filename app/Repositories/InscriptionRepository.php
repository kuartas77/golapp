<?php

declare(strict_types=1);

namespace App\Repositories;

use Exception;
use Throwable;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Notifications\InscriptionNotification;

class InscriptionRepository
{
    use ErrorTrait;

    public function __construct(private Inscription $inscription, private PeopleRepository $peopleRepository)
    {
    }

    /**
     * @param $id
     * @param false $trashed
     */
    public function findById($id, bool $trashed = false): mixed
    {
        if ($trashed) {
            return Inscription::onlyTrashed()->schoolId()->findOrFail($id);
        }

        return Inscription::query()->schoolId()->findOrFail($id);

    }

    /**
     * @param bool $created
     */
    public function createInscription(array $requestData): bool
    {
        $result = false;
        try {

            $this->setTrainingGroupId($requestData);
            $requestData['deleted_at'] = null;

            DB::beginTransaction();

            $inscription = $this->inscription->withTrashed()->updateOrCreate([
                'unique_code' => $requestData['unique_code'],
                'year' => $requestData['year'],
                'school_id' => $requestData['school_id']
            ], $requestData);

            $this->setCompetitionGroupIds($inscription, $requestData);

            $inscription->load(['player', 'school']);

            if (checkEmail(data_get($inscription, 'player.email'))) {
                $inscription->player->notifyNow(new InscriptionNotification($inscription));
            }

            DB::commit();

            $result = true;

        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError(__METHOD__, $exception);
        }

        return $result;
    }

    private function setTrainingGroupId(array &$requestData): void
    {
        $requestData['training_group_id'] = ($requestData['training_group_id'] ?? TrainingGroup::orderBy('id')->firstWhere('school_id', $requestData['school_id'])->id);
    }

    private function setCompetitionGroupIds($inscription, $requestData): void
    {
        $competitionGroupIds = data_get($requestData, 'competition_groups', []);
        if(!empty($competitionGroupIds)){
            $inscription->competitionGroup()->sync($competitionGroupIds);
        }
    }

    public function updateInscription(array $requestData, Inscription $inscription): bool
    {
        $result = false;
        try {
            $this->setTrainingGroupId($requestData);
            $requestData['deleted_at'] = null;
            $requestData['unique_code'] = $inscription->unique_code;
            $requestData['start_date'] = $inscription->start_date;

            DB::beginTransaction();

            $this->setCompetitionGroupIds($inscription, $requestData);

            $result = $inscription->update($requestData);

            DB::commit();

        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->logError(__METHOD__, $throwable);
            $result = false;
        }

        return $result;
    }

    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsEnabled()
    {
        return $this->inscription->with(['player.people', 'trainingGroup' => fn($q) => $q->withTrashed()])
            ->inscriptionYear(request('inscription_year'))->schoolId();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsDisabled()
    {
        return $this->inscription->with(['player.people', 'trainingGroup'])
            ->inscriptionYear(request('inscription_year'))->schoolId()->onlyTrashed();
    }

    public function searchInscriptionCompetition(array $fields)
    {
        return $this->inscription->query()->with('player')
            ->where('unique_code', $fields['unique_code'])
            ->where(function ($query) use ($fields): void {
                $query->where('competition_group_id', '<>', $fields['competition_group_id'])
                    ->orWhere('competition_group_id', null);
            })
            ->where('year', now()->year)
            ->first();
    }

    public function searchInsUniqueCode($id)
    {
        $inscription = $this->inscription->query()->with(['player', 'competitionGroup'])->schoolId()->findOrFail($id);
        $inscription->setRelation('competitionGroup', $inscription->competitionGroup->pluck('id'));

        return $inscription;
    }

    public function disable(Inscription $inscription): void
    {
        try {
            DB::beginTransaction();
            // $inscription->load(['payments', 'skillsControls', 'assistance']);
            // $inscription->payments()->delete();
            $inscription->skillsControls()->delete();
            $inscription->assistance()->delete();
            $inscription->tournament_payouts()->delete();
            $inscription->delete();
            DB::commit();
            alert()->success(env('APP_NAME'), __('messages.ins_delete_success'));
        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->logError("InscriptionRepository disable", $throwable);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }

    public function createInscriptionByYear($actualYear = null, $futureYear = null): void
    {
        $actualYear = $actualYear ?: now()->year;
        $futureYear = $futureYear ?: now()->addYear()->year;

        $training_group_id = TrainingGroup::query()->orderBy('id')->schoolId()->first()->id;

        $inscriptions = $this->inscription->where('year', $actualYear)->schoolId()->get();

        foreach ($inscriptions as $inscription) {

                $inscriptionData = [
                    'school_id' => $inscription->school_id,
                    'player_id' => $inscription->player_id,
                    'unique_code' => $inscription->unique_code,
                    'year' => $futureYear->year,
                    'start_date' => $futureYear->format('Y-m-d'),
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
                    'training_group_id' => $training_group_id
                ];
        }
    }

}
