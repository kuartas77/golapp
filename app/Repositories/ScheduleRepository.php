<?php

declare(strict_types=1);

namespace App\Repositories;


use App\Models\Schedule;
use App\Traits\ErrorTrait;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScheduleRepository
{
    use ErrorTrait;

    private Schedule $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }


    public function all()
    {
        return $this->schedule->query()->schoolId()->get();
    }


    /**
     * @param Request $request
     */
    public function store(array $data): void
    {
        try {
            DB::beginTransaction();
            $this->schedule->query()->create($data);
            DB::commit();

            Cache::forget('SCHEDULES_' . $data['school_id']);
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
     */
    public function update(array $data, Schedule $schedule): void
    {
        try {
            DB::beginTransaction();
            $schedule->update($data);
            DB::commit();

            Cache::forget('SCHEDULES_' . $data['school_id']);
            alert()->success(env('APP_NAME'), __('messages.schedule_create_success'));
        } catch (Exception $exception) {
            DB::rollBack();
            $this->logError("ScheduleRepository updateDay", $exception);
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
    }
}
