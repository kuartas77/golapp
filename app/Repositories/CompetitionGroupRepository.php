<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CompetitionGroup;
use App\Models\CompetitionGroupInscription;
use App\Models\Inscription;
use App\Service\Groups\GroupCatalogCache;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Throwable;

class CompetitionGroupRepository
{
    private CompetitionGroup $competitionGroup;

    public function __construct(CompetitionGroup $competitionGroup, private GroupCatalogCache $groupCatalogCache)
    {
        $this->competitionGroup = $competitionGroup;
    }

    public function listGroupEnabled(?int $schoolId = null, ?int $instructorId = null)
    {
        $schoolId ??= (int) getSchool(auth()->user())->id;
        $instructorId ??= isInstructor() ? auth()->id() : null;

        return $this->competitionGroup->query()
            ->where('school_id', $schoolId)
            ->when($instructorId, fn ($query) => $query->where('user_id', $instructorId))
            ->with('tournament', 'professor');
    }

    public function listGroupDisabled(?int $schoolId = null, ?int $instructorId = null)
    {
        $schoolId ??= (int) getSchool(auth()->user())->id;
        $instructorId ??= isInstructor() ? auth()->id() : null;

        return $this->competitionGroup->query()
            ->where('school_id', $schoolId)
            ->when($instructorId, fn ($query) => $query->where('user_id', $instructorId))
            ->onlyTrashedRelations()
            ->get();
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

            $this->clearCompetitionGroupCache((int) $dataGroup['school_id']);

            return $competitionGroup;
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->competitionGroup;
        }
    }

    public function getListGroupFullName(?int $schoolId = null, ?int $instructorId = null): Collection
    {
        $schoolId ??= (int) getSchool(auth()->user())->id;
        $instructorId ??= isInstructor() ? auth()->id() : null;

        return $this->competitionGroup->query()
            ->where('school_id', $schoolId)
            ->when($instructorId, fn ($query) => $query->where('user_id', $instructorId))
            ->with('tournament', 'professor')
            ->orderBy('name', 'ASC')->get();
    }

    public function getGroupsYear($year = null, ?int $schoolId = null, ?int $instructorId = null): Collection
    {
        $schoolId ??= (int) getSchool(auth()->user())->id;
        $instructorId ??= isInstructor() ? auth()->id() : null;
        $groups = $this->competitionGroup->query()
            ->where('school_id', $schoolId)
            ->when($instructorId, fn ($query) => $query->where('user_id', $instructorId))
            ->with('professor', 'tournament')
            ->orderBy('name', 'ASC');
        if ($year) {
            $groups->where('year', $year);
        }

        return $groups->get()->pluck('full_name_group', 'id');
    }

    public function clearCompetitionGroupCache(int $schoolId): void
    {
        $this->groupCatalogCache->invalidateSchool($schoolId);
        Cache::forget("KEY_COMPETITION_GROUPS_{$schoolId}");
    }

    public function makeRows(CompetitionGroup $competitionGroup): array
    {
        $competitionGroup->load(['inscriptions' => fn ($q) => $q->with('player')->where('year', now()->year)]);

        return [$this->rows($competitionGroup->inscriptions), $competitionGroup->inscriptions->count()];
    }

    private function rows($inscriptions): string
    {
        $rows = '';
        foreach ($inscriptions as $inscription) {
            $rows .= View::make('templates.groups.div_row', [
                'inscription' => $inscription,
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

                throw_if($exists, Exception::class, 'The member already exists in the group', 4);

                $group->inscriptions()->attach($inscription->id);
                $response = 1;
            } else {
                $group->inscriptions()->detach($inscription->id);
                $response = 2;
            }

            DB::commit();

            $this->clearCompetitionGroupCache((int) $group->school_id);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $response = $throwable->getCode();
        }

        return $response;
    }
}
