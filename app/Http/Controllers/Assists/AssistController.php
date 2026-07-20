<?php

namespace App\Http\Controllers\Assists;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssistBulkUpdateRequest;
use App\Http\Requests\AsistUpdateRequest;
use App\Models\Assist;
use App\Service\Assist\AssistActionService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistController extends Controller
{
    public function __construct(private AssistActionService $actions)
    {
    }

    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        if ($request->ajax()) {
            return response()->json($this->actions->search($request));
        }

        // return view('assists.assist.index');
        return view('assists.assist.single.index');
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        return response()->json($this->actions->create($request->only(['training_group_id', 'year', 'month'])));
    }

    public function bulkUpdate(AssistBulkUpdateRequest $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $result = $this->actions->bulkUpdate($request->validated());

        return response()->json($result['payload'], $result['status']);
    }

    public function show(Assist $assist): JsonResponse
    {
        return response()->json($this->actions->showPayload(
            $assist,
            request()->query('action'),
            request()->query('column'),
            request()->query('date')
        ));
    }

    /**
     * @param  Request  $request
     */
    public function update(AsistUpdateRequest $request, Assist $assist): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        $result = $this->actions->update($assist, $request->validated());

        return response()->json($result['payload'], $result['status']);
    }
}
