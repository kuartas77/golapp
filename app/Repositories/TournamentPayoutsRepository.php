<?php

namespace App\Repositories;

use Exception;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\CompetitionGroup;
use App\Models\TournamentPayout;
use Illuminate\Support\Facades\DB;
use App\Service\Payment\TournamentPayoutService;

class TournamentPayoutsRepository
{
    use ErrorTrait;

    protected TournamentPayoutService $service;

    public function __construct(private TournamentPayout $model)
    {
        $this->service = new TournamentPayoutService();
    }

    public function search(array $data, bool $deleted = false)
    {
        $competitionGroup = CompetitionGroup::query()->schoolId()
        ->when($deleted, fn ($q) => $q->onlyTrashedRelations())->findOrFail($data['competition_group_id']);

        $tournamentPayouts = $this->model->schoolId()->with(['inscription.player', 'tournament'])
            ->when($deleted, fn ($q) => $q->withTrashed())
            ->when(!empty($data['tournament_id']), fn ($q) => $q->where('tournament_id', $data['tournament_id']))
            ->when(!empty($data['competition_group_id']), fn ($q) => $q->where('competition_group_id', $data['competition_group_id']))
            ->when(!empty($data['unique_code']), fn ($q) => $q->where('unique_code', $data['unique_code']));

        return $this->service->generateTable($tournamentPayouts, $competitionGroup, $data, $deleted);
    }

    public function create(array $data)
    {
        $response = [];
        try {

            $tournamentPayouts = $this->model->schoolId()->with(['inscription.player', 'tournament'])
            ->when(!empty($data['tournament_id']), fn ($q) => $q->where('tournament_id', $data['tournament_id']))
            ->when(!empty($data['competition_group_id']), fn ($q) => $q->where('competition_group_id', $data['competition_group_id']));

            $data['year'] = now()->year;

            $competitionGroup = CompetitionGroup::query()->schoolId()->findOrFail($data['competition_group_id']);
            $inscriptions = Inscription::query()->select(['id', 'unique_code'])
                ->schoolId()
                ->where('competition_group_id', $data['competition_group_id'])
                ->where('year', $data['year'])->get();

            $school_id = getSchool(auth()->user())->id;

            if ($inscriptions->isNotEmpty()) {

                $ids = $tournamentPayouts->pluck('inscription_id');

                $idsDiff = $inscriptions->pluck('id')->diff($ids);

                DB::beginTransaction();
                foreach ($idsDiff as $id) {
                    $unique_code = $inscriptions->firstWhere('id', $id)->unique_code;
                    $this->model->updateOrCreate(
                        [
                            'inscription_id' => $id,
                            'year' => $data['year'],
                            'school_id' => $school_id,
                            'tournament_id' => $data['tournament_id'],
                            'competition_group_id' => $data['competition_group_id'],
                        ],
                        [
                            'inscription_id' => $id,
                            'year' => $data['year'],
                            'school_id' => $school_id,
                            'tournament_id' => $data['tournament_id'],
                            'competition_group_id' => $data['competition_group_id'],
                            'unique_code' => $unique_code
                        ]
                    );
                }
                DB::commit();
            }
            $response = $this->service->generateTable($tournamentPayouts, $competitionGroup, $data);

        } catch (Exception $th) {
            DB::rollBack();
            $this->logError("TournamentPayoutsRepository@create", $th);
        }

        return $response;
    }

    public function update(TournamentPayout $tournamentPayout, array $validated)
    {
        try {
            DB::beginTransaction();
            $updated = $tournamentPayout->update($validated);
            DB::commit();
            return $updated;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("TournamentPayoutsRepository@update", $exception);
            return false;
        }
    }
}
