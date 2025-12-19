<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\PlayerRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\CompetitionGroupRepository;

class DataTableController extends Controller
{
    public function __construct(private InscriptionRepository      $inscriptionRepository,
                                private TrainingGroupRepository    $trainingGroupRepository,
                                private CompetitionGroupRepository $competitionGroupRepository,
                                private PlayerRepository           $playerRepository,
                                private ScheduleRepository         $scheduleRepository,
                                private SchoolRepository           $schoolRepository,
                                private TrainingSessionRepository  $trainingSessionRepository
                                )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->inscriptionRepository->getInscriptionsEnabled())
        ->filterColumn('training_group_id', fn ($query, $keyword) => $query->where('training_group_id', $keyword))
        ->filterColumn('start_date', fn ($query, $keyword) => $query->where('start_date', $keyword))
        ->filterColumn('category', fn ($query, $keyword) => $query->where('category', $keyword))
        ->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function disabledInscriptions(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->inscriptionRepository->getInscriptionsDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function disabledTrainingGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->trainingGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupEnabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function disabledCompetitionGroups(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->competitionGroupRepository->listGroupDisabled())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function enabledSchedules(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->collection($this->scheduleRepository->all())->toJson();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function enabledPlayers(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        return datatables()->of($this->playerRepository->getPlayersPeople())->toJson();
    }

    public function schools(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->collection($this->schoolRepository->getAll())
            ->addColumn('logo', '{{$logo}}')
            ->addColumn('name', '{{$name}}')
            ->addColumn('agent', '{{$agent}}')
            ->addColumn('address', '{{$address}}')
            ->addColumn('phone', '{{$phone}}')
            ->addColumn('email', '{{$email}}')
            ->addColumn('is_enable', fn($model) => $model->is_enable ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>')
            ->addColumn('created_at', fn($model) => $model->created_at->format('Y-m-d'))
            ->escapeColumns([])
            ->toJson();
    }

    public function schoolsInfo(Request $request)
    {
        abort_unless($request->ajax() && isAdmin(), 403);

        return datatables()->collection($this->schoolRepository->schoolsInfo())->toJson();
    }

    public function trainingSessions(Request $request)
    {
        // abort_unless($request->ajax(), 403);



        return datatables()->collection($this->trainingSessionRepository->list())
        ->addColumn('creator', fn($model) => $model->user->name)
        ->addColumn('group', fn($model) => $model->training_group->full_group)
        ->addColumn('training_ground', '{{$training_ground}}')
        ->addColumn('period', '{{$period}}')
        ->addColumn('session', '{{$session}}')
        ->addColumn('tasks', fn($model) => $model->tasks_count)
        ->addColumn('date', fn($model) => Carbon::parse($model->date)->format('Y-m-d'))
        ->addColumn('hour', '{{$hour}}')
        ->addColumn('created_at', fn($model) => $model->created_at->format('Y-m-d'))
        ->addColumn('buttons', function($model){
            $exportPdfURL = route('export.training_sessions.pdf', [$model->id]);
            return '<div class="btn-group">'
            .'<a href="'.$exportPdfURL.'" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print" aria-hidden="true"></i></a>'
            .'</div>';
        })
        ->escapeColumns([])
        ->toJson();
    }
}
