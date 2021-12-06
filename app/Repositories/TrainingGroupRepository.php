<?php


namespace App\Repositories;


use Exception;
use App\Models\Assist;
use App\Models\Payment;
use App\Traits\ErrorTrait;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

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
        return $this->model->query()->school()->with('schedule.day', 'professor')->get();
    }

    public function listGroupDisabled()
    {
        return $this->model->query()->onlyTrashedRelations()->school()->get();
    }

    /**
     * @param $request
     * @param $create
     * @param null $trainingGroup
     * @return mixed
     */
    public function setTrainingGroup(FormRequest $request, bool $create, TrainingGroup $trainingGroup = null)
    {
        $group = $this->setTrainingGroupParams($request);

        DB::beginTransaction();
        try {
            if ($create) {
                $trainingGroup = $this->model->create($group);
            } else {
                $trainingGroup->update($group);
            }
            DB::commit();
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
            'schedule_id' => $request->input('schedule_id'),
            'day_id' => $request->input('day_id'),
            'school_id' => $request->input('school_id')
        ];
    }

    /**
     * @param TrainingGroup $trainingGroup
     * @return TrainingGroup
     */
    public function getTrainingGroup(TrainingGroup $trainingGroup): Model
    {
        $trainingGroup->load('schedule.day', 'professor');

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
     * @param $inscription_id
     * @param $request
     * @return bool
     */
    public function assignTrainingGroup($inscription_id, $request): bool
    {
        $origin_group = $request->input('origin_group', null);
        $target_group = $request->input('target_group', null);
        $inscription = Inscription::query()->find($inscription_id);
        if (is_null($target_group) || empty($inscription)) {
            return false;
        }

        $date = now();
        $year = $date->year;
        $month = getMonth($date->month);

        DB::beginTransaction();
        try {
            Payment::query()->updateOrCreate(
                [
                    'inscription_id' => $inscription->id,
                    'year' => $year,
                ],
                [
                    'training_group_id' => $target_group,
                    'unique_code' => $inscription->unique_code
                ]
            );

            Assist::query()->updateOrCreate(
                [
                    'inscription_id' => $inscription->id,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'inscription_id' => $inscription->id,
                    'year' => $year,
                    'month' => $month,
                    'training_group_id' => $target_group,
                ]
            );
            $state = $inscription->update(['training_group_id' => $target_group]);
            DB::commit();
            return $state;
        } catch (Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * @param bool $deleted
     * @param null $whereUser
     * @return Collection
     */
    public function getListGroupsSchedule($deleted = false, $whereUser = null): Collection
    {
        if ($deleted) {
            $query = $this->model->query()->school()->onlyTrashedRelations();
        } else {
            $query = $this->model->query()->school()->with('schedule.day');
        }

        if ($whereUser) {
            $query->where('user_id', $whereUser);
        }
        return $query->orderBy('name', 'ASC')
            ->get()->pluck('full_schedule_group', 'id');
    }

    /**
     * @return mixed
     */
    public function historicAssistData()
    {
        return $this->model->query()
        ->whereHas('assists', fn ($query) => $query->withTrashed()->where('year', '<', now()->year))
        ->onlyTrashedRelationsFilter()
        ->orderBy('created_at', 'desc')
        ->get()
        ->each(fn ($group) => $group->assists->setAppends(['url_historic','months']) );
    }

    /**
     * @return mixed
     */
    public function historicPaymentData()
    {
        return $this->model->query()
        ->whereHas('payments', fn ($query) => $query->withTrashed()->where('year', '<', now()->year))
        ->onlyTrashedRelationsPayments()
        ->orderBy('created_at', 'desc')
        ->get()
        ->each(fn ($group) => $group->payments->setAppends(['url_historic']));
    }

    /**
     * @param $year
     * @return Collection
     */
    public function getGroupsYear($year): Collection
    {
        return $this->model->query()->with('schedule.day')
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
        $trainingGroup->load(['inscriptions'=>function($q){
            $q->with('player')->where('year', now()->year);
        }]);
        $rows = '';
        foreach ($trainingGroup->inscriptions as $inscription) {
            $rows .= View::make('templates.groups.div_row', [
                'inscription' => $inscription
            ])->render();
        }
        return $rows;
    }

}
