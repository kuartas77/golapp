<?php

namespace App\Service\DataTables;

use App\Repositories\TrainingSessionRepository;
use App\Service\InstructorPeriodEditPolicy;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TrainingSessionDataTableService
{
    public function __construct(private TrainingSessionRepository $sessions, private InstructorPeriodEditPolicy $periodPolicy) {}

    public function sessions(): JsonResponse
    {
        return $this->build($this->sessions->datatableQuery(), fn ($model) => route('export.training_sessions.pdf', [$model->id]));
    }

    public function plannings(): JsonResponse
    {
        return $this->build($this->sessions->plannedDatatableQuery(), fn ($model) => route('session-plannings.pdf', $model->id));
    }

    private function build($query, callable $pdfUrl): JsonResponse
    {
        return datatables()->eloquent($query)
            ->filterColumn('creator_name', fn ($query, $keyword) => $query->where('users.name', 'like', "%{$keyword}%"))
            ->filterColumn('training_group_name', fn ($query, $keyword) => $query->where('training_groups.name', 'like', "%{$keyword}%"))
            ->orderColumn('creator_name', 'users.name $1')->orderColumn('training_group_name', 'training_groups.name $1')
            ->addColumn('creator_name', fn ($model) => $model->user?->name ?? '')
            ->addColumn('training_group_name', fn ($model) => $model->training_group?->full_group ?? '')
            ->editColumn('date', fn ($model) => Carbon::parse($model->date)->format('Y-m-d'))
            ->editColumn('created_at', fn ($model) => $model->created_at?->format('Y-m-d'))
            ->addColumn('period_locked', fn ($model) => !$this->periodPolicy->canMutateDate($model->date))
            ->addColumn('export_pdf_url', $pdfUrl)->toJson();
    }
}
