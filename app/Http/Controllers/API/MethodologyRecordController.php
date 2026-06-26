<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\MethodologyRecordRequest;
use App\Models\MethodologyRecord;
use App\Repositories\MethodologyRecordRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MethodologyRecordController extends Controller
{
    public function __construct(private MethodologyRecordRepository $repository)
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
        $record = $this->repository->create($request->validated());

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
        $record = $this->repository->update($record, $request->validated());

        return response()->json([
            'message' => 'Registro metodológico actualizado correctamente.',
            'data' => $this->serialize($record),
        ]);
    }

    public function destroy(int $methodologyRecord): JsonResponse
    {
        $record = $this->repository->findAccessibleOrFail($methodologyRecord);
        $this->repository->destroy($record);

        return response()->json([
            'message' => 'Registro metodológico eliminado correctamente.',
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
            'created_at' => $record->created_at?->format('Y-m-d'),
            'updated_at' => $record->updated_at?->format('Y-m-d'),
            'export_pdf_url' => route('methodology.records.pdf', ['id' => $record->id]),
        ];
    }
}
