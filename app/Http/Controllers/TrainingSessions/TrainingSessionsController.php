<?php

namespace App\Http\Controllers\TrainingSessions;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\TrainingSessionsRequest;
use App\Repositories\TrainingSessionRepository;
use Illuminate\Contracts\Foundation\Application;

class TrainingSessionsController extends Controller
{
    public function __construct(private TrainingSessionRepository $repository)
    {
        //
    }

    public function index(): Factory|View|Application
    {
        return view('training_sessions.index');
    }

    public function create(): Factory|View|Application
    {
        $numberTasks = 0;
        return view('training_sessions.create', compact('numberTasks'));
    }

    public function store(TrainingSessionsRequest $request)
    {
        $trainingSession = $this->repository->store($request->validated());

        if($trainingSession){
            alert()->success(env('APP_NAME'), __('messages.training_session_created'));
            return redirect()->to(route('training-sessions.index'));
        }

        alert()->error(env('APP_NAME'), __('messages.error_general'));
        return back()->withInput($request->input());
    }
}
