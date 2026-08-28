<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\MethodologyRecordRequest;
use App\Models\MethodologyRecord;
use App\Repositories\MethodologyRecordRepository;
use App\Service\DataTables\MethodologyDataTableService;
use App\Service\InstructorPeriodEditPolicy;
use App\Service\Methodology\VisualResourceImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MethodologyRecordController extends Controller
{
    public function __construct(
        private MethodologyRecordRepository $repository,
        private InstructorPeriodEditPolicy $periodEditPolicy,
        private VisualResourceImageService $visualImages,
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', Rule::in(MethodologyRecord::TYPES)],
        ]);

        return response()->json([
            'data' => $this->repository
                ->list($validated['type'] ?? null)
                ->map(fn (MethodologyRecord $record) => $this->serialize($record))
                ->values(),
        ]);
    }

    public function store(MethodologyRecordRequest $request): JsonResponse
    {
        [$payload, $newPaths] = $this->visualImages->methodologyPayload(
            $request->validated(),
            $request->all(),
            $request->allFiles(),
        );

        try {
            $record = $this->repository->create($payload);
        } catch (\Throwable $throwable) {
            $this->visualImages->deleteMany($newPaths);
            throw $throwable;
        }

        return response()->json([
            'message' => 'Registro metodológico creado correctamente.',
            'data' => $this->serialize($record->fresh(['user:id,name', 'trainingGroup:id,name,category'])),
        ], Response::HTTP_CREATED);
    }

    public function show(int $methodologyRecord): JsonResponse
    {
        return response()->json([
            'data' => $this->serialize($this->repository->findAccessibleOrFail($methodologyRecord)),
        ]);
    }

    public function update(MethodologyRecordRequest $request, int $methodologyRecord): JsonResponse
    {
        $record = $this->repository->findAccessibleOrFail($methodologyRecord);
        $this->periodEditPolicy->assertCanMutateDate($this->recordDate($record), 'period');
        [$payload, $newPaths, $deleteAfterCommit] = $this->visualImages->methodologyPayload(
            $request->validated(),
            $request->all(),
            $request->allFiles(),
            $record,
        );

        try {
            $record = $this->repository->update($record, $payload);
        } catch (\Throwable $throwable) {
            $this->visualImages->deleteMany($newPaths);
            throw $throwable;
        }

        $this->visualImages->deleteMany($deleteAfterCommit);

        return response()->json([
            'message' => 'Registro metodológico actualizado correctamente.',
            'data' => $this->serialize($record),
        ]);
    }

    public function destroy(int $methodologyRecord): JsonResponse
    {
        $record = $this->repository->findAccessibleOrFail($methodologyRecord);
        $this->periodEditPolicy->assertCanMutateDate($this->recordDate($record), 'period');
        $deleteAfterCommit = collect($record->diagram_media ?? [])->pluck('path')->filter()->all();
        $this->repository->destroy($record);
        $this->visualImages->deleteMany($deleteAfterCommit);

        return response()->json([
            'message' => 'Registro metodológico eliminado correctamente.',
        ]);
    }

    public function filters(MethodologyDataTableService $dataTables): JsonResponse
    {
        return response()->json([
            'data' => $dataTables->filters(),
        ]);
    }

    private function serialize(MethodologyRecord $record): array
    {
        return [
            'id' => $record->id,
            'school_id' => $record->school_id,
            'user_id' => $record->user_id,
            'creator_name' => $record->user?->name,
            'training_group_id' => $record->training_group_id,
            'training_group_name' => $record->trainingGroup?->name,
            'type' => $record->type,
            'title' => $record->title,
            'fields' => $record->fields ?? [],
            'diagrams' => $record->diagrams ?? [],
            'diagram_media' => $this->serializeDiagramMedia($record->diagram_media ?? []),
            'session_date' => $this->recordDate($record),
            'created_at' => $record->created_at?->format('Y-m-d'),
            'updated_at' => $record->updated_at?->format('Y-m-d'),
            'period_locked' => ! $this->periodEditPolicy->canMutateDate($this->recordDate($record)),
            'export_pdf_url' => route('methodology.records.pdf', ['id' => $record->id]),
        ];
    }

    private function recordDate(MethodologyRecord $record): ?string
    {
        return data_get($record->fields ?? [], 'session_date') ?: $record->created_at?->format('Y-m-d');
    }

    private function serializeDiagramMedia(array $media): array
    {
        return collect($media)->map(function ($item): array {
            $path = is_array($item) ? ($item['path'] ?? null) : null;

            return [
                'mode' => (is_array($item) && ($item['mode'] ?? null) === VisualResourceImageService::MODE_IMAGE)
                    ? VisualResourceImageService::MODE_IMAGE
                    : VisualResourceImageService::MODE_DIAGRAM,
                'path' => $path,
                'image_url' => $this->visualImages->url($path),
            ];
        })->all();
    }
}
