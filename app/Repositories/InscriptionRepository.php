<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Inscription;
use App\Models\TrainingGroup;
use App\Notifications\InscriptionNotification;
use App\Repositories\PeopleRepository;
use App\Traits\ErrorTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

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

            $requestData['training_group_id'] = $this->getTrainingGroupId($requestData);
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

    public function getTrainingGroupId(array &$requestData): mixed
    {
        $trainingGroup = TrainingGroup::query()
            ->orderBy('id')
            ->firstWhere('school_id', $requestData['school_id']);

        throw_if(is_null($trainingGroup), Exception::class, 'Training group not found for school');
        $requestData['training_group_id'] = isset($requestData['training_group_id']) ? $requestData['training_group_id'] : $trainingGroup->id;
        $requestData['pre_inscription'] = $requestData['training_group_id'] == $trainingGroup->id;

        return isset($requestData['training_group_id']) ? $requestData['training_group_id'] : $trainingGroup->id;
    }

    private function setCompetitionGroupIds($inscription, $requestData): void
    {
        $competitionGroupIds = data_get($requestData, 'competition_groups', []);

        $inscription->competitionGroup()->sync($competitionGroupIds);
    }

    public function updateInscription(array $requestData, Inscription $inscription): bool
    {
        $result = false;
        try {
            $requestData['training_group_id'] = $this->getTrainingGroupId($requestData);
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
    public function getInscriptionsEnabled(): Builder
    {
        return Inscription::query()->select('inscriptions.*')->with(['player.people', 'trainingGroup' => fn($q) => $q->withTrashed()])
            ->join('players', 'inscriptions.player_id', '=', 'players.id')
            ->inscriptionYear(request('inscription_year'))
            ->schoolId();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsDisabled(): Builder
    {
        return $this->inscription->with(['player.people', 'trainingGroup'])
            ->inscriptionYear(request('inscription_year'))->schoolId()->onlyTrashed();
    }

    public function searchInscriptionCompetition(array $fields): ?Inscription
    {
        return Inscription::query()->with('player')
            ->where('unique_code', $fields['unique_code'])
            ->whereHas(
                'competitionGroup',
                fn($q)=> $q->where('competition_group_id', $fields['competition_group_id']), '<=', 0)
            ->where('year', now()->year)
            ->schoolId()
            ->first();
    }

    public function searchInsUniqueCode($id): ?Inscription
    {
        $query = $this->inscription->query()
            ->with(['player', 'competitionGroup'])
            ->schoolId();

        $inscription = null;

        if (is_numeric($id)) {
            $inscription = (clone $query)->find((int) $id);
        }

        if (!$inscription) {
            $inscription = $query
                ->orderByDesc('id')
                ->firstWhere('unique_code', (string) $id);
        }

        if (!$inscription) {
            return null;
        }

        $inscription->setAttribute(
            'competition_groups',
            $inscription->competitionGroup->pluck('id')->map(fn ($groupId) => (string) $groupId)->values()->all()
        );

        return $inscription;
    }

    public function disable(Inscription $inscription): bool
    {
        try {
            DB::beginTransaction();
            $inscription->load(['payments']);

            foreach($inscription->payments as $payment) {
                $payment->january = $payment->january == '0' ? '6':$payment->january;
                $payment->february = $payment->february == '0' ? '6':$payment->february;
                $payment->march = $payment->march == '0' ? '6':$payment->march;
                $payment->april = $payment->april == '0' ? '6':$payment->april;
                $payment->may = $payment->may == '0' ? '6':$payment->may;
                $payment->june = $payment->june == '0' ? '6':$payment->june;
                $payment->july = $payment->july == '0' ? '6':$payment->july;
                $payment->august = $payment->august == '0' ? '6':$payment->august;
                $payment->september = $payment->september == '0' ? '6':$payment->september;
                $payment->october = $payment->october == '0' ? '6':$payment->october;
                $payment->november = $payment->november == '0' ? '6':$payment->november;
                $payment->december = $payment->december == '0' ? '6':$payment->december;
                $payment->save();
            }

            $inscription->payments()->delete();
            $inscription->skillsControls()->delete();
            $inscription->assistance()->delete();
            $inscription->tournament_payouts()->delete();
            $inscription->delete();
            DB::commit();
            return true;
        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->logError("InscriptionRepository disable", $throwable);
            return false;
        }
    }

    public function createInscriptionByYear($actualYear = null, $futureYear = null): void
    {
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

            $trainingGroup = TrainingGroup::query()->orderBy('id')->schoolId()->first();
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
                    'training_group_id' => $trainingGroup->id
                ];

                $this->inscription->withTrashed()->updateOrCreate([
                    'unique_code' => $inscriptionData['unique_code'],
                    'year' => $inscriptionData['year'],
                    'school_id' => $inscriptionData['school_id']
                ], $inscriptionData);
            }

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->logError(__METHOD__, $throwable);
        }
    }

    public function getPreinscriptionsOrProvicionalGroup($schoolId, $trainingGroupId): Builder
    {
        return Inscription::query()
            ->select([
                'inscriptions.id',
                'inscriptions.unique_code',
                DB::raw("CONCAT(players.names, ' ', players.last_names) as names")
            ])
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->where('inscriptions.year', now()->year)
            ->where('inscriptions.school_id', $schoolId)
            ->where(
                fn($query) => $query->where('inscriptions.training_group_id', $trainingGroupId)
                    ->orWhere('inscriptions.pre_inscription', 1));
    }

}
