<?php

namespace App\Service\DataTables;

use App\Models\MethodologyRecord;
use App\Models\TrainingGroup;
use App\Models\User;
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
            ->orderColumn('training_group_name', 'training_groups.name $1')
            ->orderColumn('session_date', fn ($query, $order) => $this->orderBySessionDate($query, $order))
            ->addColumn('creator_name', fn (MethodologyRecord $record) => $record->user?->name ?? '')
            ->addColumn('training_group_name', fn (MethodologyRecord $record) => $record->trainingGroup?->name ?? '')
            ->addColumn('session_date', fn (MethodologyRecord $record) => $this->recordDate($record))
            ->editColumn('created_at', fn (MethodologyRecord $record) => $record->created_at?->format('Y-m-d'))
            ->addColumn('period_locked', fn (MethodologyRecord $record) => !$this->periodPolicy->canMutateDate($this->recordDate($record)))
            ->addColumn('export_pdf_url', fn (MethodologyRecord $record) => route('methodology.records.pdf', ['id' => $record->id]))->toJson();
        $payload = $response->getData(true);
        return response()->json($payload);
    }

    public function filters(): array
    {
        $schoolId = (int) getSchool(auth()->user())->id;
        $creators = $this->creatorFilters($schoolId);
        $groups = TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->select(['id', 'name', 'stage', 'days', 'schedules'])
            ->whereNotNull('name')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->map(fn (TrainingGroup $group) => [
                'value' => $group->name,
                'label' => $group->full_schedule_group ?: $group->full_group ?: $group->name,
            ])
            ->values();

        return ['creators' => $creators, 'training_groups' => $groups];
    }

    private function creatorFilters(int $schoolId)
    {
        if (isInstructor()) {
            return collect([auth()->user()?->name])
                ->filter()
                ->map(fn (string $name) => ['value' => $name, 'label' => $name])
                ->values();
        }

        return User::query()
            ->select('users.name')
            ->whereNotNull('users.name')
            ->where(function ($query) use ($schoolId) {
                $query->where('users.school_id', $schoolId)
                    ->orWhereExists(function ($exists) use ($schoolId) {
                        $exists->selectRaw('1')
                            ->from('schools_user')
                            ->whereColumn('schools_user.user_id', 'users.id')
                            ->where('schools_user.school_id', $schoolId);
                    })
                    ->orWhereExists(function ($exists) use ($schoolId) {
                        $exists->selectRaw('1')
                            ->from('methodology_records')
                            ->whereColumn('methodology_records.user_id', 'users.id')
                            ->where('methodology_records.school_id', $schoolId)
                            ->whereNull('methodology_records.deleted_at');
                    });
            })
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->unique()
            ->map(fn (string $name) => ['value' => $name, 'label' => $name])
            ->values();
    }

    private function recordDate(MethodologyRecord $record): ?string
    {
        return data_get($record->fields ?? [], 'session_date') ?: $record->created_at?->format('Y-m-d');
    }

    private function orderBySessionDate($query, string $order): void
    {
        $direction = strtolower($order) === 'asc' ? 'asc' : 'desc';
        $driver = $query->getConnection()->getDriverName();
        $expression = $driver === 'sqlite'
            ? "COALESCE(json_extract(methodology_records.fields, '$.session_date'), date(methodology_records.created_at))"
            : "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(methodology_records.fields, '$.session_date')), DATE(methodology_records.created_at))";

        $query->orderByRaw("{$expression} {$direction}");
    }
}
