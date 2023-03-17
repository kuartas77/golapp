<?php


namespace App\Repositories;


use Exception;
use App\Models\Schedule;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleRepository
{
    use ErrorTrait;
    /**
     * @var Schedule
     */
    private Schedule $model;

    public function __construct(Schedule $model)
    {
        $this->model = $model;
    }


    public function all()
    {
        return $this->model->query()->schoolId()->get();
    }


    /**
     * @param Request $request
     */
    public function store(array $data)
    {
        try {
            DB::beginTransaction();
            $this->model->query()->create($data);
            DB::commit();

            Cache::forget("SCHEDULES_{$data['school_id']}");
            alert()->success(env('APP_NAME'), __('messages.schedule_create_success'));
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("ScheduleRepository store", $exception);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }

    /**
     * @param Request $request
     * @param Day $day
     * @return bool|RedirectResponse
     */
    public function update(array $data, Schedule $schedule)
    {
        try {
            DB::beginTransaction();
            $schedule->update($data);
            DB::commit();

            Cache::forget("SCHEDULES_{$data['school_id']}");
            alert()->success(env('APP_NAME'), __('messages.schedule_create_success'));
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("ScheduleRepository updateDay", $exception);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }
}
