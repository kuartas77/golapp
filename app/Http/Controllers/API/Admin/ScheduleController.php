<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        $schedules = Schedule::query()
            ->schoolId()
            ->orderBy('schedule')
            ->get(['id', 'schedule'])
            ->map(fn (Schedule $schedule) => $this->transformSchedule($schedule))
            ->values();

        return response()->json($schedules);
    }

    public function show(int $id): JsonResponse
    {
        return $this->responseJson(
            $this->transformSchedule($this->findSchedule($id))
        );
    }

    public function store(ScheduleRequest $request): JsonResponse
    {
        return $this->persistSchedule(
            new Schedule(),
            $request,
            'Horario creado correctamente.',
            201
        );
    }

    public function update(int $id, ScheduleRequest $request): JsonResponse
    {
        return $this->persistSchedule(
            $this->findSchedule($id),
            $request,
            'Horario actualizado correctamente.'
        );
    }

    public function destroy(int $id): Response
    {
        $schedule = $this->findSchedule($id);
        $schoolId = $schedule->school_id;

        $schedule->delete();

        Cache::forget("SCHEDULES_{$schoolId}");

        return response()->noContent();
    }

    private function persistSchedule(
        Schedule $schedule,
        ScheduleRequest $request,
        string $message,
        int $status = 200
    ): JsonResponse {
        try {
            $schedule->fill($request->validated());
            $schedule->school_id = getSchool(auth()->user())->id;
            $schedule->save();

            Cache::forget("SCHEDULES_{$schedule->school_id}");

            return response()->json([
                'message' => $message,
                'data' => $this->transformSchedule($schedule->fresh()),
            ], $status);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible guardar el horario.',
            ], 500);
        }
    }

    private function findSchedule(int $id): Schedule
    {
        return Schedule::query()
            ->schoolId()
            ->findOrFail($id);
    }

    private function transformSchedule(Schedule $schedule): array
    {
        $parts = preg_split('/\s*-\s*/', (string) $schedule->schedule, 2) ?: [];

        return [
            'id' => $schedule->id,
            'schedule' => $schedule->schedule,
            'schedule_start' => $this->formatScheduleSegmentForInput((string) ($parts[0] ?? '')),
            'schedule_end' => $this->formatScheduleSegmentForInput((string) ($parts[1] ?? '')),
        ];
    }

    private function formatScheduleSegmentForInput(string $value): string
    {
        $normalized = strtoupper((string) preg_replace('/\s+/', '', trim($value)));

        if (preg_match('/^(\d{1,2}:\d{2})(AM|PM|M)$/', $normalized, $matches) === 1) {
            $meridiem = $matches[2] === 'M' ? 'PM' : $matches[2];

            return sprintf('%s %s', $matches[1], $meridiem);
        }

        return trim($value);
    }
}
