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

    /**
     * @var AssistRepository
     */
    private $repository;

    public function __construct(AssistRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return array|Application|Factory|View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->repository->search($request, false);
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

        return response()->json($this->repository->create($request));
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

        return response()->json($this->repository->update($assist, $request));
    }
}
