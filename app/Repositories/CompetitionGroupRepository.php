<?php


namespace App\Repositories;


use App\Traits\Fields;
use App\Traits\ErrorTrait;
use App\Models\CompetitionGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
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

    public function createOrUpdateTeam(FormRequest $request, bool $create = true , $competitionGroup = null): Model
    {
        try {
            DB::beginTransaction();
        
            if ($create) { 
                $competitionGroup = $this->model->create($request->validated()); 
            } 
            else { 
                $competitionGroup->update($request->validated()); 
            }

            DB::commit();

            Cache::forget("KEY_COMPETITION_GROUPS_{$request->input('school_id')}");

            return $competitionGroup;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError("CompetitionGroupRepository createOrUpdateTeam", $th);
            return $this->model;
        }
    }

    /**
     * @return Collection
     */
    public function getListGroupFullName(): Collection
    {
        return $this->model->query()->schoolId()
            ->with('tournament', 'professor')
            ->orderBy('name','ASC')
            ->get()->pluck('full_name_group', 'id');
    }

    /**
     * @param null $year
     * @return Collection
     */
    public function getGroupsYear($year = null): Collection
    {
        $groups =  $this->model->query()->schoolId()->with('professor','tournament')
            ->orderBy('name','ASC');
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
}
