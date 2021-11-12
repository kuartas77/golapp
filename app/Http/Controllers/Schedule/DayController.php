<?php

namespace App\Http\Controllers\Schedule;

use Exception;
use App\Models\Day;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\DayRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class DayController extends Controller
{
    /**
     * @var DayRepository
     */
    private $repository;

    public function __construct(DayRepository $repository)
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
        return view('day.index');
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
    public function store(Request $request): RedirectResponse
    {
        $this->repository->store($request);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param Day $day
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Day $day, Request $request): JsonResponse
    {
        abort_if(!$request->ajax(), 404);

        $day->load('schedules');
        $response = collect();
        $day->schedules->sortBy('id')->map(function ($schedule) use (&$response) {
            $response->push([
                'id' => $schedule->id,
                'name' => $schedule->schedule
            ]);
        });

        return response()->json(['data' => $response]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Day $day
     * @return JsonResponse
     */
    public function edit(Day $day): JsonResponse
    {
        $day->load('schedules')->loadCount('schedules');
        return $this->responseJson($day);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Day $day
     * @return RedirectResponse
     */
    public function update(Request $request, Day $day): RedirectResponse
    {
        $this->repository->updateDay($request, $day);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Day $day
     * @return void
     * @throws Exception
     */
    public function destroy(Day $day)
    {
        if ($day->delete()) {
            alert()->success(env('APP_NAME'), __('messages.day_delete_success'));
        } else {
            alert()->error(env('APP_NAME'), __('messages.ins_create_failure'));
        }
        abort(404);
    }

}
