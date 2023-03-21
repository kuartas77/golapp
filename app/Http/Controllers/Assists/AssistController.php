<?php

namespace App\Http\Controllers\Assists;


use App\Models\Assist;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\AssistRepository;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\AsistUpdateRequest;
use Illuminate\Contracts\Foundation\Application;

class AssistController extends Controller
{
    private AssistRepository $repository;

    public function __construct(AssistRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return Application|Factory|\Illuminate\Contracts\View\View|JsonResponse
     */
    public function index(Request $request): Application|Factory|\Illuminate\Contracts\View\View|JsonResponse
    {
        if ($request->ajax()) {
            return response()->json($this->repository->search($request->only(['training_group_id', 'year', 'month'])));
        }
        return view('assists.assist.index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        return response()->json($this->repository->create($request->only(['training_group_id', 'year', 'month'])));
    }

    /**
     * @param Assist $assist
     * @return JsonResponse
     */
    public function show(Assist $assist): JsonResponse
    {
        return response()->json($assist->load(['player']));
    }

    /**
     * @param Request $request
     * @param Assist $assist
     * @return JsonResponse
     */
    public function update(AsistUpdateRequest $request, Assist $assist): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        return response()->json($this->repository->update($assist, $request->validated()));
    }
}
