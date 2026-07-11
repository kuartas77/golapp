<?php

namespace App\Service\DataTables;

use App\Models\MethodologyRecord;
use App\Models\TrainingGroup;
use App\Repositories\MethodologyRecordRepository;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Http\JsonResponse;

class MethodologyDataTableService
{
    public function __construct(private MethodologyRecordRepository $records, private InstructorPeriodEditPolicy $periodPolicy) {}

    public function records(?string $type): JsonResponse
    {
        $response = datatables()->eloquent($this->records->datatableQuery($type))
            ->filterColumn('title', fn ($query, $keyword) => $query->where('methodology_records.title', 'like', "%{$keyword}%"))
            ->filterColumn('creator_name', fn ($query, $keyword) => $query->where('users.name', 'like', "%{$keyword}%"))
            ->filterColumn('training_group_name', fn ($query, $keyword) => $query->where('training_groups.name', 'like', "%{$keyword}%"))
            ->orderColumn('title', 'methodology_records.title $1')->orderColumn('creator_name', 'users.name $1')
            ->orderColumn('training_group_name', 'training_groups.name $1')->orderColumn('created_at', 'methodology_records.created_at $1')
            ->addColumn('creator_name', fn (MethodologyRecord $record) => $record->user?->name ?? '')
            ->addColumn('training_group_name', fn (MethodologyRecord $record) => $record->trainingGroup?->name ?? '')
            ->editColumn('created_at', fn (MethodologyRecord $record) => $record->created_at?->format('Y-m-d'))
            ->addColumn('period_locked', fn (MethodologyRecord $record) => !$this->periodPolicy->canMutateDate($record->created_at))
            ->addColumn('export_pdf_url', fn (MethodologyRecord $record) => route('methodology.records.pdf', ['id' => $record->id]))->toJson();
        $payload = $response->getData(true);
        $payload['filters'] = $this->filters();
        return response()->json($payload);
    }

    private function filters(): array
    {
        $school = getSchool(auth()->user());
        $creators = (isInstructor() ? collect([auth()->user()]) : $school->users()->select('users.name')->orderBy('users.name')->get())
            ->pluck('name')->filter()->unique()->map(fn (string $name) => ['value' => $name, 'label' => $name])->values();
        $groups = TrainingGroup::query()->schoolId()->select('name')->whereNotNull('name')->orderBy('name')->pluck('name')->unique()
            ->map(fn (string $name) => ['value' => $name, 'label' => $name])->values();
        return ['creators' => $creators, 'training_groups' => $groups];
    }
}
