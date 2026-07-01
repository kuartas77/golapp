<?php

namespace App\Http\Controllers\Assists;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssistBulkUpdateRequest;
use App\Http\Requests\AsistUpdateRequest;
use App\Models\Assist;
use App\Repositories\AssistRepository;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistController extends Controller
{
    private AssistRepository $repository;

    public function __construct(AssistRepository $repository, private InstructorPeriodEditPolicy $periodEditPolicy)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        if ($request->ajax()) {
            $search = $this->repository->search(params: $request->only(['training_group_id', 'year', 'month', 'column']), raw: $request->filled('dataRaw'));

            return response()->json($search);
        }

        // return view('assists.assist.index');
        return view('assists.assist.single.index');
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        return response()->json($this->repository->create($request->only(['training_group_id', 'year', 'month'])));
    }

    public function bulkUpdate(AssistBulkUpdateRequest $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $validated = $request->validated();

        abort_if(
            isInstructor() && ! instructorCanAccessTrainingGroup((int) $validated['training_group_id'], (int) $validated['year']),
            404
        );

        $this->periodEditPolicy->assertCanMutateYearMonth($validated['year'], $validated['month'], 'assist');

        if ((int) $validated['year'] !== (int) now()->year) {
            $message = 'Las asistencias de años anteriores son de sólo lectura.';

            return response()->json([
                'message' => $message,
                'errors' => [
                    'assist' => [$message],
                ],
            ], 422);
        }

        return response()->json([
            'data' => $this->repository->bulkUpdate($validated),
        ]);
    }

    public function show(Assist $assist): JsonResponse
    {
        abort_if(
            isInstructor() && ! instructorCanAccessTrainingGroup($assist->training_group_id, (int) $assist->year),
            404
        );

        $assist->load(['player']);

        $action = request()->query('action');

        if ($action === 'observation') {

            $observations = '';
            if (is_object($assist->observations)) {
                foreach ($assist->observations as $date => $observation) {
                    $observations .= $date.': '.$observation.PHP_EOL;
                }
            }

            return response()->json([
                'id' => $assist->id,
                'player_name' => $assist->player->full_names,
                'observations' => $observations,
            ]);
        } else {
            $column = request()->query('column');
            $date = request()->query('date');

            return response()->json([
                'id' => $assist->id,
                'player_name' => $assist->player->full_names,
                'value' => data_get($assist, $column),
                'observation' => data_get($assist, "observations.{$date}", ''),
            ]);
        }
    }

    /**
     * @param  Request  $request
     */
    public function update(AsistUpdateRequest $request, Assist $assist): JsonResponse
    {
        abort_unless($request->ajax(), 404);
        abort_if(
            isInstructor() && ! instructorCanAccessTrainingGroup($assist->training_group_id, (int) $assist->year),
            404
        );

        if ($this->repository->assistBelongsToDeletedInscription($assist)) {
            return response()->json([
                'message' => AssistRepository::RETIRED_INSCRIPTION_MESSAGE,
                'errors' => [
                    'assist' => [AssistRepository::RETIRED_INSCRIPTION_MESSAGE],
                ],
            ], 422);
        }

        $this->periodEditPolicy->assertCanMutateYearMonth(
            (int) $assist->year,
            (int) $assist->getRawOriginal('month'),
            'assist'
        );

        if ((int) $assist->year !== (int) now()->year) {
            $message = 'Las asistencias de años anteriores son de sólo lectura.';

            return response()->json([
                'message' => $message,
                'errors' => [
                    'assist' => [$message],
                ],
            ], 422);
        }

        return response()->json($this->repository->update($assist, $request->validated()));
    }
}
