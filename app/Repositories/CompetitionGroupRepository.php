<?php

declare(strict_types=1);

namespace App\Repositories;


use Throwable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\CompetitionGroupInscription;
use App\Models\CompetitionGroup;

class CompetitionGroupRepository
{
    use ErrorTrait;

    private CompetitionGroup $competitionGroup;

    public function __construct(CompetitionGroup $competitionGroup)
    {
        $this->competitionGroup = $competitionGroup;
    }

    public function listGroupEnabled()
    {
        return $this->competitionGroup->query()->schoolId()->with('tournament', 'professor');
    }

    public function listGroupDisabled()
    {
        return $this->competitionGroup->query()->schoolId()->onlyTrashedRelations()->get();
    }

    public function createOrUpdateTeam(array $dataGroup, bool $create = true, ?CompetitionGroup $competitionGroup = null): Model
    {
        try {
            DB::beginTransaction();

            if ($create) {
                $competitionGroup = $this->competitionGroup->create($dataGroup);
            } else {
                $competitionGroup->update($dataGroup);
            }

            DB::commit();

            Cache::forget('KEY_COMPETITION_GROUPS_' . $dataGroup['school_id']);

            return $competitionGroup;
        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->logError("CompetitionGroupRepository@createOrUpdateTeam", $throwable);
            return $this->competitionGroup;
        }
    }

    public function getListGroupFullName(): Collection
    {
        return $this->competitionGroup->query()->schoolId()->with('tournament', 'professor')
            ->orderBy('name', 'ASC')->get();
    }

    public function getGroupsYear($year = null): Collection
    {
        $groups = $this->competitionGroup->query()->schoolId()->with('professor', 'tournament')->orderBy('name', 'ASC');
        if ($year) {
            $groups->where('year', $year);
        }

        return $groups->get()->pluck('full_name_group', 'id');
    }

    public function makeRows(CompetitionGroup $competitionGroup): array
    {
        $competitionGroup->load(['inscriptions' => fn ($q) => $q->with('player')->where('year', now()->year)]);
        return [$this->rows($competitionGroup->inscriptions), $competitionGroup->inscriptions->count()];
    }

    /**
     * @param $inscriptions
     */
    private function rows($inscriptions): string
    {
        $rows = '';
        foreach ($inscriptions as $inscription) {
            $rows .= View::make('templates.groups.div_row', [
                'inscription' => $inscription
            ])->render();
        }

        return $rows;
    }

    public function makeInscriptionRows($inscriptions): array
    {
        return [$this->rows($inscriptions), $inscriptions->count()];
    }

    public function assignInscriptionGroup(string $inscription_id, string $destination_group_id, bool $assign)
    {
        $response = 3;
        try {
            $group = CompetitionGroup::without(['inscriptions'])->findOrfail($destination_group_id);
            $inscription = Inscription::select(['id'])->findOrFail($inscription_id);


            DB::beginTransaction();

            if ($assign) {
                $exists = CompetitionGroupInscription::query()
                    ->where('competition_group_id', $destination_group_id)
                    ->where('inscription_id', $inscription_id)->exists();

                throw_if($exists, Exception::class, "The member already exists in the group", 4);

                $group->inscriptions()->attach($inscription->id);
                $response = 1;
            } else {
                $group->inscriptions()->detach($inscription->id);
                $response = 2;
            }

            DB::commit();

        } catch (Throwable $throwable) {
            DB::rollBack();
            $this->logError("CompetitionGroupRepository@assignInscriptionGroup", $throwable);
            $response = $throwable->getCode();
        }

        return $response;
    }
}
