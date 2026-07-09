<?php

declare(strict_types=1);

namespace App\Http\Requests\API;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SessionPlanningUpsertRequest extends FormRequest
{
    private const DIAGRAM_TYPES = [
        'player',
        'player_token',
        'cone',
        'ball',
        'hoop',
        'arrow',
        'pass',
        'dribble',
        'off_ball_run',
        'cross',
        'xmark',
        'text',
    ];

    public function authorize(): bool
    {
        return isSchool() || isInstructor() || isAdmin();
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'],
            'training_group_id' => ['required', 'integer', Rule::exists('training_groups', 'id')->where(fn ($query) => $query->where('school_id', $this->currentSchoolId()))],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'period' => ['required', 'string', 'max:100'],
            'session' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'hour' => ['required', 'string', 'max:20'],
            'training_ground' => ['nullable', 'string', 'max:100'],
            'material' => ['nullable', 'string'],
            'warm_up' => ['nullable', 'string'],
            'back_to_calm' => ['nullable', 'string', 'max:10'],
            'players' => ['nullable', 'string'],
            'absences' => ['nullable', 'string'],
            'incidents' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
            'sync_attendance' => ['required', 'boolean'],
            'absence_inscription_ids' => ['nullable', 'array'],
            'absence_inscription_ids.*' => ['integer', 'distinct'],
            'phases' => ['required', 'array', 'min:1', 'max:4'],
            'phases.*.position' => ['required', 'integer', 'between:1,4', 'distinct'],
            'phases.*.name' => ['required', 'string', 'max:100'],
            'phases.*.time' => ['nullable', 'string', 'max:50'],
            'phases.*.dosage' => ['nullable', 'string'],
            'phases.*.description' => ['nullable', 'string'],
            'phases.*.diagram' => ['nullable', 'array'],
            'phases.*.diagram.*.type' => ['required', Rule::in(self::DIAGRAM_TYPES)],
            'phases.*.diagram.*.x' => ['required', 'numeric', 'between:0,100'],
            'phases.*.diagram.*.y' => ['required', 'numeric', 'between:0,64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $date = (string) $this->input('date', now()->toDateString());
        $phases = collect($this->input('phases', []))->values()->map(fn ($phase, $index) => [
            'position' => $index + 1,
            'name' => $this->normalizeString($phase['name'] ?? null),
            'time' => $this->normalizeString($phase['time'] ?? null),
            'dosage' => $this->normalizeString($phase['dosage'] ?? null),
            'description' => $this->normalizeString($phase['description'] ?? null),
            'diagram' => array_values($phase['diagram'] ?? []),
        ])->all();

        $this->merge([
            'school_id' => $this->currentSchoolId(),
            'user_id' => auth()->id(),
            'year' => Carbon::parse($date)->year,
            'date' => $date,
            'hour' => $this->input('hour', '02:00 PM'),
            'period' => $this->normalizeString($this->input('period')),
            'session' => $this->normalizeString($this->input('session')),
            'training_ground' => $this->normalizeString($this->input('training_ground')),
            'material' => $this->normalizeString($this->input('material')),
            'warm_up' => $this->normalizeString($this->input('warm_up')),
            'back_to_calm' => $this->normalizeString($this->input('back_to_calm')),
            'players' => $this->normalizeString($this->input('players')),
            'absences' => $this->normalizeString($this->input('absences')),
            'incidents' => $this->normalizeString($this->input('incidents')),
            'feedback' => $this->normalizeString($this->input('feedback')),
            'sync_attendance' => true,
            'absence_inscription_ids' => collect($this->input('absence_inscription_ids', []))->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'phases' => $phases,
        ]);
    }

    private function currentSchoolId(): int
    {
        return getSchool(auth()->user())->id;
    }

    private function normalizeString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }
}
