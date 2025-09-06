<?php

declare(strict_types=1);

namespace App\Repositories;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use App\Traits\ErrorTrait;
use App\Models\TrainingGroup;

/**
 * @method static create(array $group)
 */
class TrainingGroupRepository
{
    use ErrorTrait;

    private TrainingGroup $trainingGroup;

    public function __construct(TrainingGroup $trainingGroup)
    {
        $this->trainingGroup = $trainingGroup;
    }

    public function listGroupEnabled()
    {
        $firstTeam = $this->trainingGroup->query()->select(['id'])->schoolId()->orderBy('id', 'ASC')->first();
        return $this->trainingGroup->query()
            ->schoolId()
            ->with(['instructors'])
            ->where(fn ($query) =>
                $query->whereRelation('instructors', 'assigned_year', '>=', now()->year)
                ->orWhere('id', $firstTeam->id)
                ->orWhere('year_active', '>=', now()->year)
            )
            ->get();
    }

    public function listGroupDisabled()
    {
        return $this->trainingGroup->query()
            ->onlyTrashedRelations()
            ->schoolId()
            ->whereRelation('instructors', fn ($query) => $query->where('assigned_year', '<', now()->year))
            ->where('year_active', '<', now()->year)
            ->get();
    }


    public function createTrainingGroup(FormRequest $formRequest): ?TrainingGroup
    {
        $group = $this->setTrainingGroupParams($formRequest);

        try {

            DB::beginTransaction();

            $userInstructors = $group['user_id'];
            unset($group['user_id']);
            $trainingGroup = new TrainingGroup($group);
            $trainingGroup->save();
            $trainingGroup->instructors()->syncWithPivotValues($userInstructors, ['assigned_year' => $formRequest->input('year_active', now()->year)]);

            DB::commit();

            Cache::forget('KEY_TRAINING_GROUPS_' . $formRequest->input('school_id'));

            return $trainingGroup;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("TrainingGroupRepository setTrainingGroup", $exception);
            return null;
        }
    }

    /**
     * @param $request
     */
    private function setTrainingGroupParams(FormRequest $formRequest): array
    {
        return [
            'name' => $formRequest->input('name'),
            'stage' => $formRequest->input('stage'),
            'user_id' => $formRequest->input('user_id'),
            'category' => array_map('categoriesName', $formRequest->input('years')),
            'year' => $formRequest->input('years.0', null),
            'year_two' => $formRequest->input('years.1', null),
            'year_three' => $formRequest->input('years.2', null),
            'year_four' => $formRequest->input('years.3', null),
            'year_five' => $formRequest->input('years.4', null),
            'year_six' => $formRequest->input('years.5', null),
            'year_seven' => $formRequest->input('years.6', null),
            'year_eight' => $formRequest->input('years.7', null),
            'year_nine' => $formRequest->input('years.8', null),
            'year_ten' => $formRequest->input('years.9', null),
            'year_eleven' => $formRequest->input('years.10', null),
            'year_twelve' => $formRequest->input('years.11', null),
            'schedules' => $formRequest->input('schedules', []),
            'days' => $formRequest->input('days', []),
            'school_id' => $formRequest->input('school_id'),
            'year_active' => $formRequest->input('year_active')
        ];
    }

    public function updateTrainingGroup(FormRequest $formRequest, TrainingGroup $trainingGroup): ?TrainingGroup
    {
        $group = $this->setTrainingGroupParams($formRequest);

        try {
            DB::beginTransaction();

            $userInstructors = $group['user_id'];
            unset($group['user_id']);
            $trainingGroup->update($group);
            $trainingGroup->instructors()->syncWithPivotValues($userInstructors, ['assigned_year' => $formRequest->input('year_active', now()->year)]);

            DB::commit();

            Cache::forget('KEY_TRAINING_GROUPS_' . $formRequest->input('school_id'));

            return $trainingGroup;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("TrainingGroupRepository setTrainingGroup", $exception);
            return null;
        }
    }

    /**
     * @return TrainingGroup
     */
    public function getTrainingGroup(TrainingGroup $trainingGroup): Model
    {
        $trainingGroup->load(['instructors']);

        $years = collect();
        $trainingGroup->year == null ?: $years->push($trainingGroup->year);
        for ($i = 2; $i <= 12; ++$i) {
            $number = numbersToLetters($i, false);
            is_null($trainingGroup->$number) ?: $years->push($trainingGroup->$number);
        }

        $trainingGroup->years = $years;
        return $trainingGroup;
    }

    /**
     * @param null $whereUser
     */
    public function getListGroupsSchedule(bool $deleted = false, ?int $user_id = null, ?callable $filter = null): Collection
    {
        $query = $this->trainingGroup->query()->schoolId()->where('year_active', '>=', now()->year);
        if ($deleted) {
            $query->onlyTrashedRelations()
                ->whereRelation('instructors', fn ($query) => $query->where('assigned_year', '<', now()->year));
        } elseif ($user_id) {
            $query->whereRelation('instructors', function ($query) use ($user_id): void {
                $query->where('training_group_user.user_id', $user_id)
                    ->where('assigned_year', now()->year);
            });
        } else {
            $firstTeam = $this->trainingGroup->query()->select(['id'])->schoolId()->orderBy('id', 'ASC')->first();
            $query->where(function ($query) use ($firstTeam): void {
                $query->whereRelation('instructors', 'assigned_year', '>=', now()->year)
                    ->orWhere('id',  $firstTeam->id);
            });
        }

        $result = $query->orderBy('name', 'ASC')->get();

        if (!is_null($filter)) {
            $result = $filter($result);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function historicAssistData()
    {
        return $this->trainingGroup->query()->schoolId()
            ->whereHas('assists', fn ($query) => $query->withTrashed()->where('year', '<', now()->year))
            ->onlyTrashedRelationsFilter()
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn ($group) => $group->assists->setAppends(['url_historic', 'months']));
    }

    /**
     * @return mixed
     */
    public function historicPaymentData()
    {
        return $this->trainingGroup->query()->schoolId()
            ->whereHas('payments', fn ($query) => $query->withTrashed()->where('year', '<', now()->year))
            ->onlyTrashedRelationsPayments()
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn ($group) => $group->payments->setAppends(['url_historic']));
    }

    /**
     * @param $year
     */
    public function getGroupsYear($year): Collection
    {
        return $this->trainingGroup->query()->schoolId()
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

    public function makeRows(TrainingGroup $trainingGroup): string
    {
        $trainingGroup->load(['inscriptions' => fn ($q) => $q->with('player')->where('year', now()->year)]);
        $rows = '';
        foreach ($trainingGroup->inscriptions as $inscription) {
            $rows .= View::make('templates.groups.div_row', [
                'inscription' => $inscription
            ])->render();
        }

        return $rows;
    }

    public static function getClassDays($group, $month = null): Collection
    {
        if($month) {
            $month = getMonthNumber($month);
            $date = Carbon::now()->setMonth($month);
        }else{
            $date = Carbon::now();
        }

        $classDays = classDays(
            $date->year,
            $date->month,
            array_map('dayToNumber', $group->explode_days)
        );

        return $classDays->map(function ($classDay)use($group, $date) {
            $name = Str::ucfirst($classDay['name']);
            return [
                'id' => "{$group->id}{$date->month}{$classDay['day']}",
                'date' => $classDay['day'],
                'day' => $name,
                'month' => $date->month,
                'month_name' => getMonth($date->month),
                'column' => $classDay['column'],
                'group_id' => $group->id,
                'school_id' => getSchool(auth()->user())->id,
                'year' => $date->year
            ];
        });
    }
}
