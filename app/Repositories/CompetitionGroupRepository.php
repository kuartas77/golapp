<?php


namespace App\Repositories;


use App\Traits\Fields;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\CompetitionGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\CompetitionGroupInscription;
use Illuminate\Foundation\Http\FormRequest;

class CompetitionGroupRepository
{
    use Fields;
    use ErrorTrait;

    /**
     * @var CompetitionGroup
     */
    private CompetitionGroup $model;

    public function __construct(CompetitionGroup $model)
    {
        $this->model = $model;
    }

    public function listGroupEnabled()
    {
        return $this->model->query()->schoolId()->with('tournament', 'professor')->get();
    }

    public function listGroupDisabled()
    {
        return $this->model->query()->schoolId()->onlyTrashedRelations()->get();
    }

    public function createOrUpdateTeam(array $dataGroup, bool $create = true , ?CompetitionGroup $competitionGroup = null): Model
    {
        try {
            DB::beginTransaction();
        
            if ($create) { 
                $competitionGroup = $this->model->create($dataGroup); 
            } 
            else { 
                $competitionGroup->update($dataGroup); 
            }

            DB::commit();

            Cache::forget("KEY_COMPETITION_GROUPS_{$dataGroup['school_id']}");

            return $competitionGroup;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError("CompetitionGroupRepository@createOrUpdateTeam", $th);
            return $this->model;
        }
    }

    /**
     * @return Collection
     */
    public function getListGroupFullName(): Collection
    {
        return $this->model->query()->schoolId()->with('tournament', 'professor')
            ->orderBy('name','ASC')->get()->pluck('full_name_group', 'id');
    }

    /**
     * @param null $year
     * @return Collection
     */
    public function getGroupsYear($year = null): Collection
    {
        $groups =  $this->model->query()->schoolId()->with('professor','tournament')->orderBy('name','ASC');
        if($year){
            $groups->where('year', $year);
        }
        return $groups->get()->pluck('full_name_group', 'id');
    }

    /**
     * @param CompetitionGroup $competitionGroup
     * @return array
     */
    public function makeRows(CompetitionGroup $competitionGroup): array
    {
        $competitionGroup->load(['inscriptions'=>function($q){
            $q->with('player')->where('year', now()->year);
        }]);
        return [$this->rows($competitionGroup->inscriptions), $competitionGroup->inscriptions->count()];
    }

    public function makeInscriptionRows($inscriptions): array
    {
        return [$this->rows($inscriptions), $inscriptions->count()];
    }

    /**
     * @param $inscriptions
     * @return string
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

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError("CompetitionGroupRepository@assignInscriptionGroup", $th);
            $response = $th->getCode();
        }

        return $response;
    }
}
