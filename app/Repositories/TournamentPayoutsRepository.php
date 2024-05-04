<?php

namespace App\Repositories;

use App\Models\CompetitionGroup;
use App\Models\CompetitionGroupInscription;
use App\Models\Inscription;
use App\Models\TournamentPayout;
use App\Service\Payment\TournamentPayoutService;
use App\Traits\ErrorTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TournamentPayoutsRepository
{
    use ErrorTrait;

    protected TournamentPayoutService $service;

    public function __construct(private TournamentPayout $model)
    {
        $this->service = new TournamentPayoutService();
    }

    public function search(array $data, bool $wantsJson = false, bool $deleted = false)
    {
        $competitionGroup = CompetitionGroup::query()->schoolId()
            ->when($deleted, fn($q) => $q->onlyTrashedRelations())->findOrFail($data['competition_group_id']);

        $tournamentPayouts = $this->model->schoolId()->with(['inscription.player', 'tournament'])
            ->when($deleted, fn($q) => $q->withTrashed())
            ->when(!empty($data['tournament_id']), fn($q) => $q->where('tournament_id', $data['tournament_id']))
            ->when(!empty($data['competition_group_id']), fn($q) => $q->where('competition_group_id', $data['competition_group_id']))
            ->when(!empty($data['unique_code']), fn($q) => $q->where('unique_code', $data['unique_code']));

        if($wantsJson){
            return $this->service->generateData($tournamentPayouts, $competitionGroup, $data, $deleted);
        }

        return $this->service->generateTable($tournamentPayouts, $competitionGroup, $data, $deleted);
    }

    public function filterSelect(array $data, bool $deleted = false): Builder
    {
        $query = $this->model->query()->schoolId()->with(['inscription.player', 'tournament']);

        if ($deleted) {
            $query = $this->model->schoolId()->with([
                'inscription' => fn($query) => $query->with(['player'])->withTrashed()
            ])->withTrashed();
        }

        $query->where('tournament_id', $data['tournament_id'])
            ->where('competition_group_id', $data['competition_group_id'])
            ->when(!empty($data['year']), fn($q) => $q->where('year', $data['year']))
            ->when(!empty($data['unique_code']), fn($q) => $q->where('unique_code', $data['unique_code']))
            ->orderBy('inscription_id', 'asc');

        return $query;
    }

    public function create(array $data)
    {
        $response = [];
        try {

            $data['year'] = now()->year;

            $tournamentPayouts = $this->model->schoolId()->with(['inscription.player', 'tournament'])
                ->when(!empty($data['tournament_id']), fn($q) => $q->where('tournament_id', $data['tournament_id']))
                ->when(!empty($data['competition_group_id']), fn($q) => $q->where('competition_group_id', $data['competition_group_id']));

            $competitionGroup = CompetitionGroup::query()->schoolId()->findOrFail($data['competition_group_id']);

            $inscriptionIds = CompetitionGroupInscription::select(['inscription_id'])->where('competition_group_id', $data['competition_group_id'])->get();

            $inscriptionIds = $inscriptionIds->pluck('inscription_id');

            $inscriptions = Inscription::query()->select(['id', 'unique_code'])
                ->schoolId()
                ->whereIn('id', $inscriptionIds)
                ->where('year', $data['year'])->get();

            $school_id = getSchool(auth()->user())->id;

            if ($inscriptionIds->isNotEmpty()) {

                $ids = $tournamentPayouts->pluck('inscription_id');

                $idsDiff = $inscriptionIds->diff($ids);

                DB::beginTransaction();
                foreach ($idsDiff as $id) {
                    $unique_code = $inscriptions->firstWhere('id', $id)->unique_code ?? null;
                    if (!$unique_code) {
                        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
                        logger("inscription deshabilitada {$id}");
                        continue;
                    }
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
