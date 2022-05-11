<?php


namespace App\Repositories;


use Exception;
use App\Models\Day;
use App\Models\Schedule;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class DayRepository
{
    use ErrorTrait;
    /**
     * @var Day
     */
    private Day $model;

    public function __construct(Day $model)
    {
        $this->model = $model;
    }


    public function all()
    {
        $days = $this->model->query()->whereRelation('schedules', 'school_id', auth()->user()->school_id)->with('schedules')->get();
        $days->setAppends(['schedul']);
        return $days;
    }


    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $day = $this->model->query()->create(['days' => $this->getDaysClass()]);

            foreach ($request->input('schedule') as $schedule) {
                if (!is_null($schedule['value'])) {
                    $day->schedules()->create([
                        'schedule' => $schedule['value'],
                        'school_id' => auth()->user()->school_id
                    ]);
                }
            }

            DB::commit();
            alert()->success(env('APP_NAME'), __('messages.day_create_success'));
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("DayRepository store", $exception);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }

    /**
     * @param Request $request
     * @param Day $day
     * @return bool|RedirectResponse
     */
    public function updateDay(Request $request, Day $day)
    {
        try {
            DB::beginTransaction();
            $day->update(['days' => $this->getDaysClass()]);

            foreach ($request->input('schedule') as $schedule) {
                if (!is_null($schedule['value'])) {
                    Schedule::query()->find($schedule['id'])->update(['schedule' => $schedule['value']]);
                }
            }

            DB::commit();
            alert()->success(env('APP_NAME'), __('messages.day_create_success'));
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("DayRepository updateDay", $exception);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }

    private function getDaysClass(): string
    {
        $days_class = collect();
        $days_class->push(request('day_one'));
        is_null(request('day_two')) ?: $days_class->push(request('day_two'));
        is_null(request('day_three')) ?: $days_class->push(request('day_three'));
        is_null(request('day_four')) ?: $days_class->push(request('day_four'));
        is_null(request('day_five')) ?: $days_class->push(request('day_five'));
        return $days_class->implode(',');
    }
}
