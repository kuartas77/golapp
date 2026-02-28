<?php

namespace App\Http\Controllers\TrainingSessions;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingSessionsRequest;
use App\Models\TrainingSession;
use App\Repositories\TrainingSessionRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

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

    public function store(TrainingSessionsRequest $request): RedirectResponse
    {
        $trainingSession = $this->repository->store($request->validated());

        if($trainingSession){
            Alert::success(env('APP_NAME'), __('messages.training_session_created'));
            return redirect()->to(route('training-sessions.index'));
        }

        Alert::error(env('APP_NAME'), __('messages.error_general'));
        return back()->withInput($request->input());
    }

    public function show(TrainingSession $trainingSession): JsonResponse
    {
        $trainingSession->load('tasks');

        return response()->json($trainingSession);
    }

    public function update(TrainingSession $trainingSession, TrainingSessionsRequest $request): RedirectResponse
    {
        $trainingSession = $this->repository->update($trainingSession, $request->validated());

        if($trainingSession){
            Alert::success(env('APP_NAME'), __('Actualizado'));
            return redirect()->to(route('training-sessions.index'));
        }

        Alert::error(env('APP_NAME'), __('messages.error_general'));
        return back()->withInput($request->input());
    }
}
