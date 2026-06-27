<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingSessionUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return isSchool() || isInstructor() || isAdmin();
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'training_group_id' => [
                'required',
                'integer',
                Rule::exists('training_groups', 'id')->where(
                    fn ($query) => $query->where('school_id', $this->currentSchoolId())
                ),
            ],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'period' => ['required', 'string', 'max:100'],
            'session' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'hour' => ['required', 'string', 'max:20'],
            'training_ground' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string'],
            'back_to_calm' => ['nullable', 'string', 'max:10'],
            'players' => ['nullable', 'string'],
            'absences' => ['nullable', 'string'],
            'incidents' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
            'warm_up' => ['nullable', 'string'],
            'coaches' => ['nullable', 'string'],
            'sync_attendance' => ['required', 'boolean'],
            'absence_inscription_ids' => ['nullable', 'array'],
            'absence_inscription_ids.*' => ['integer', 'distinct'],
            'tasks' => ['required', 'array', 'size:3'],
            'tasks.*.task_number' => ['required', 'integer', 'between:1,3', 'distinct'],
            'tasks.0.task_name' => ['required', 'string', 'max:100'],
            'tasks.1.task_name' => ['nullable', 'string', 'max:100'],
            'tasks.2.task_name' => ['nullable', 'string', 'max:100'],
            'tasks.*.general_objective' => ['nullable', 'string', 'max:50'],
            'tasks.*.specific_goal' => ['nullable', 'string', 'max:50'],
            'tasks.*.content_one' => ['nullable', 'string', 'max:50'],
            'tasks.*.content_two' => ['nullable', 'string', 'max:50'],
            'tasks.*.content_three' => ['nullable', 'string', 'max:50'],
            'tasks.*.ts' => ['nullable', 'string', 'max:10'],
            'tasks.*.sr' => ['nullable', 'string', 'max:10'],
            'tasks.*.tt' => ['nullable', 'string', 'max:10'],
            'tasks.*.observations' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $date = $this->input('date', now()->toDateString());
        $tasks = collect($this->input('tasks', []))->values();

        $normalizedTasks = collect(range(0, 2))
            ->map(function (int $index) use ($tasks): array {
                $task = $tasks->get($index, []);

                return [
                    'task_number' => (int) ($task['task_number'] ?? ($index + 1)),
                    'task_name' => $this->normalizeString($task['task_name'] ?? null),
                    'general_objective' => $this->normalizeString($task['general_objective'] ?? null),
                    'specific_goal' => $this->normalizeString($task['specific_goal'] ?? null),
                    'content_one' => $this->normalizeString($task['content_one'] ?? null),
                    'content_two' => $this->normalizeString($task['content_two'] ?? null),
                    'content_three' => $this->normalizeString($task['content_three'] ?? null),
                    'ts' => $this->normalizeString($task['ts'] ?? null),
                    'sr' => $this->normalizeString($task['sr'] ?? null),
                    'tt' => $this->normalizeString($task['tt'] ?? null),
                    'observations' => $this->normalizeString($task['observations'] ?? null),
                ];
            })
            ->all();

        $this->merge([
            'school_id' => $this->currentSchoolId(),
            'user_id' => auth()->id(),
            'date' => $date,
            'hour' => $this->input('hour', now()->format('h:i A')),
            'year' => $this->resolveYear($date),
            'period' => $this->normalizeString($this->input('period')),
            'session' => $this->normalizeString($this->input('session')),
            'training_ground' => $this->normalizeString($this->input('training_ground')),
            'material' => $this->normalizeString($this->input('material')),
            'back_to_calm' => $this->normalizeString($this->input('back_to_calm')),
            'players' => $this->normalizeString($this->input('players')),
            'absences' => $this->normalizeString($this->input('absences')),
            'incidents' => $this->normalizeString($this->input('incidents')),
            'feedback' => $this->normalizeString($this->input('feedback')),
            'warm_up' => $this->normalizeString($this->input('warm_up')),
            'coaches' => $this->normalizeString($this->input('coaches')),
            'sync_attendance' => true,
            'absence_inscription_ids' => collect($this->input('absence_inscription_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'tasks' => $normalizedTasks,
        ]);
    }

    private function currentSchoolId(): int
    {
        return getSchool(auth()->user())->id;
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function resolveYear(string $date): int
    {
        try {
            return Carbon::parse($date)->year;
        } catch (\Throwable) {
            return (int) now()->year;
        }
    }
}
