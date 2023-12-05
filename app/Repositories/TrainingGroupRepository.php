<?php


namespace App\Repositories;


use App\Models\TrainingGroup;
use App\Traits\ErrorTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * @method static create(array $group)
 */
class TrainingGroupRepository
{
    use ErrorTrait;

    private TrainingGroup $model;

    public function __construct(TrainingGroup $model)
    {
        $this->model = $model;
    }

    public function listGroupEnabled()
    {
        return $this->model->query()->schoolId()->with(['instructors'])->get();
    }

    public function listGroupDisabled()
    {
        return $this->model->query()->onlyTrashedRelations()->schoolId()->get();
    }

    /**
     * @param $request
     * @param $create
     * @param null $trainingGroup
     * @return mixed
     */
    public function createTrainingGroup(FormRequest $request)
    {
        $group = $this->setTrainingGroupParams($request);

        try {

            DB::beginTransaction();

            $userInstructors = $group['user_id'];
            unset($group['user_id']);
            $trainingGroup = new TrainingGroup($group);
            $trainingGroup->save();
            $trainingGroup->instructors()->syncWithPivotValues($userInstructors, ['assigned_year' => now()->year]);

            DB::commit();

            Cache::forget("KEY_TRAINING_GROUPS_{$request->input('school_id')}");

            return $trainingGroup;

        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("TrainingGroupRepository setTrainingGroup", $exception);
            return null;
        }
    }

    /**
     * @param $request
     * @return array
     */
    private function setTrainingGroupParams($request): array
    {
        return [
            'name' => $request->input('name'),
            'stage' => $request->input('stage'),
            'user_id' => $request->input('user_id'),
            'category' => array_map('categoriesName', $request->input('years')),
            'year' => $request->input('years.0', null),
            'year_two' => $request->input('years.1', null),
            'year_three' => $request->input('years.2', null),
            'year_four' => $request->input('years.3', null),
            'year_five' => $request->input('years.4', null),
            'year_six' => $request->input('years.5', null),
            'year_seven' => $request->input('years.6', null),
            'year_eight' => $request->input('years.7', null),
            'year_nine' => $request->input('years.8', null),
            'year_ten' => $request->input('years.9', null),
            'year_eleven' => $request->input('years.10', null),
            'year_twelve' => $request->input('years.11', null),
            'schedules' => $request->input('schedules', []),
            'days' => $request->input('days', []),
            'school_id' => $request->input('school_id')
        ];
    }

    public function updateTrainingGroup(FormRequest $request, TrainingGroup $trainingGroup)
    {
        $group = $this->setTrainingGroupParams($request);

        try {
            DB::beginTransaction();

            $userInstructors = $group['user_id'];
            unset($group['user_id']);
            $trainingGroup->update($group);
            $trainingGroup->instructors()->syncWithPivotValues($userInstructors, ['assigned_year' => now()->year]);

            DB::commit();

            Cache::forget("KEY_TRAINING_GROUPS_{$request->input('school_id')}");

            return $trainingGroup;

        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("TrainingGroupRepository setTrainingGroup", $exception);
            return null;
        }
    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return TrainingGroup
     */
    public function getTrainingGroup(TrainingGroup $trainingGroup): Model
    {
        $trainingGroup->load(['instructors' => function($instructors){
            $instructors->where('assigned_year', now()->year);
        }]);

        $years = collect();
        $trainingGroup->year == null ?: $years->push($trainingGroup->year);
        for ($i = 2; $i <= 12; $i++) {
            $number = numbersToLetters($i, false);
            is_null($trainingGroup->$number) ?: $years->push($trainingGroup->$number);
        }

        $trainingGroup->years = $years;
        return $trainingGroup;
    }

    /**
     * @param bool $deleted
     * @param null $whereUser
     * @return Collection
     */
    public function getListGroupsSchedule($deleted = false, $user_id = null): Collection
    {
        if ($deleted) {
            $query = $this->model->query()->schoolId()->onlyTrashedRelations();
        } else {
            $query = $this->model->query()->schoolId();
        }

        if ($user_id) {
            $query->whereRelation('instructors', function($query) use($user_id){
                $query->where('training_group_user.user_id', $user_id)
                ->where('assigned_year', now()->year);
            });
        }
        return $query->orderBy('name', 'ASC')
            ->get()->pluck('full_schedule_group', 'id');
    }

    /**
     * @return mixed
     */
    public function historicAssistData()
    {
        return $this->model->query()->schoolId()
            ->whereHas('assists', fn($query) => $query->withTrashed()->where('year', '<', now()->year))
            ->onlyTrashedRelationsFilter()
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn($group) => $group->assists->setAppends(['url_historic', 'months']));
    }

    /**
     * @return mixed
     */
    public function historicPaymentData()
    {
        return $this->model->query()->schoolId()
            ->whereHas('payments', fn($query) => $query->withTrashed()->where('year', '<', now()->year))
            ->onlyTrashedRelationsPayments()
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn($group) => $group->payments->setAppends(['url_historic']));
    }

    /**
     * @param $year
     * @return Collection
     */
    public function getGroupsYear($year): Collection
    {
        return $this->model->query()->schoolId()
            ->where('year', $year)
            ->orWhere('year_two', $year)
            ->orWhere('year_three', $year)
            ->orWhere('year_four', $year)
            ->orWhere('year_five', $year)
            ->orWhere('year_six', $year)
            ->orWhere('year_seven', $year)
            ->orWhere('year_eight', $year)
            ->orWhere('year_nine', $year)
            ->orWhere('year_ten', $year)
            ->orWhere('year_eleven', $year)
            ->orWhere('year_twelve', $year)
            ->orderBy('name', 'ASC')
            ->get()
            ->pluck('full_schedule_group', 'id');
    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return string
     */
    public function makeRows(TrainingGroup $trainingGroup): string
    {
        $trainingGroup->load(['inscriptions' => fn($q) => $q->with('player')->where('year', now()->year)]);
        $rows = '';
        foreach ($trainingGroup->inscriptions as $inscription) {
            $rows .= View::make('templates.groups.div_row', [
                'inscription' => $inscription
            ])->render();
        }
        return $rows;
    }

}
