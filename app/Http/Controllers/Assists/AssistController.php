<?php

namespace App\Http\Controllers\Assists;


use App\Http\Controllers\Controller;
use App\Http\Requests\AsistUpdateRequest;
use App\Models\Assist;
use App\Repositories\AssistRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistController extends Controller
{
    private AssistRepository $repository;

    public function __construct(AssistRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|JsonResponse
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        if ($request->ajax()) {
            return response()->json($this->repository->search($request->only(['training_group_id', 'year', 'month', 'column'])));
        }
        // return view('assists.assist.index');
        return view('assists.assist.single.index');
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
        $assist->load(['player']);

        $action = request()->query('action');

        if ($action === 'observation') {

            $observations = '';
            if(is_object($assist->observations)) {
                foreach($assist->observations as $date => $observation){
                    $observations .= $date .': '. $observation. PHP_EOL;
                }
            }

            $assist->observations = $observations;

            return response()->json($assist);
        }else {
            $column = request()->query('column');
            $date = request()->query('date');

            return response()->json([
                'id' => $assist->id,
                'player_name' => $assist->player->full_names,
                'value' => data_get($assist, $column),
                'observation' => data_get($assist, "observations.{$date}", '')
            ]);
        }
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
