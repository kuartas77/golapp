<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Admin\SchoolDataExportResource;
use App\Jobs\GenerateSchoolDataExport;
use App\Models\School;
use App\Models\SchoolDataExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SchoolDataExportController extends Controller
{
    public function index(School $school): JsonResponse
    {
        $exports = SchoolDataExport::query()
            ->with(['requester:id,name,email', 'school:id,slug'])
            ->where('school_id', $school->id)
            ->latest()
            ->limit(20)
            ->get();

        $this->markExpired($exports);

        return response()->json([
            'data' => SchoolDataExportResource::collection($exports->fresh(['requester:id,name,email', 'school:id,slug'])),
        ]);
    }

    public function store(Request $request, School $school): JsonResponse
    {
        $activeExport = SchoolDataExport::query()
            ->with(['requester:id,name,email', 'school:id,slug'])
            ->where('school_id', $school->id)
            ->whereIn('status', [
                SchoolDataExport::STATUS_PENDING,
                SchoolDataExport::STATUS_PROCESSING,
            ])
            ->latest()
            ->first();

        if ($activeExport) {
            return response()->json([
                'message' => 'Ya hay una exportacion en proceso para esta escuela.',
                'data' => new SchoolDataExportResource($activeExport),
            ], Response::HTTP_CONFLICT);
        }

        $schoolDataExport = SchoolDataExport::query()->create([
            'school_id' => $school->id,
            'requested_by' => $request->user()?->id,
            'status' => SchoolDataExport::STATUS_PENDING,
            'disk' => 'export',
            'expires_at' => now()->addDays(7),
        ]);

        GenerateSchoolDataExport::dispatch($schoolDataExport->id)->onQueue('golapp_default');

        return response()->json([
            'message' => 'La exportacion fue solicitada correctamente.',
            'data' => new SchoolDataExportResource($schoolDataExport->load(['requester:id,name,email', 'school:id,slug'])),
        ], Response::HTTP_CREATED);
    }

    public function show(School $school, SchoolDataExport $dataExport): JsonResponse
    {
        $this->authorizeSchoolExport($school, $dataExport);
        $this->markExpired(collect([$dataExport]));

        return response()->json([
            'data' => new SchoolDataExportResource($dataExport->fresh(['requester:id,name,email', 'school:id,slug'])),
        ]);
    }

    public function download(School $school, SchoolDataExport $dataExport): BinaryFileResponse
    {
        $this->authorizeSchoolExport($school, $dataExport);
        $this->markExpired(collect([$dataExport]));
        $dataExport = $dataExport->fresh();

        abort_unless($dataExport?->isReadyForDownload(), Response::HTTP_NOT_FOUND);
        abort_unless(Storage::disk($dataExport->disk)->exists($dataExport->path), Response::HTTP_NOT_FOUND);

        return response()->download(
            Storage::disk($dataExport->disk)->path($dataExport->path),
            $dataExport->filename ?: basename($dataExport->path)
        );
    }

    private function authorizeSchoolExport(School $school, SchoolDataExport $dataExport): void
    {
        abort_unless((int) $dataExport->school_id === (int) $school->id, Response::HTTP_NOT_FOUND);
    }

    private function markExpired($exports): void
    {
        foreach ($exports as $export) {
            if ($export->status === SchoolDataExport::STATUS_READY && $export->isExpired()) {
                $export->forceFill(['status' => SchoolDataExport::STATUS_EXPIRED])->save();
            }
        }
    }
}
