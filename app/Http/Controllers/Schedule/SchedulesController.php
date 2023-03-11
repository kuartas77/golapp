<?php

namespace App\Http\Controllers\Schedule;

use Exception;
use App\Models\Schedule;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ScheduleRequest;
use Illuminate\Contracts\View\Factory;
use App\Repositories\ScheduleRepository;
use Illuminate\Contracts\Foundation\Application;

class SchedulesController extends Controller
{
    /**
     * @var ScheduleRepository
     */
    private $repository;

    public function __construct(ScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('schedules.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(ScheduleRequest $request): RedirectResponse
    {
        $this->repository->store($request);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param Schedule $schedule
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Schedule $schedule, Request $request): JsonResponse
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Schedule $schedule
     * @return JsonResponse
     */
    public function edit(Schedule $schedule): JsonResponse
    {
        return $this->responseJson($schedule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Schedule $schedule
     * @return RedirectResponse
     */
    public function update(ScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $this->repository->update($request, $schedule);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Schedule $schedule
     * @return void
     * @throws Exception
     */
    public function destroy(Schedule $schedule)
    {
        if ($schedule->delete()) {
            alert()->success(env('APP_NAME'), __('messages.schedule_delete_success'));
        } else {
            alert()->error(env('APP_NAME'), __('messages.error_general'));
        }
        abort(404);
    }

}
