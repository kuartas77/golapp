<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolDocumentStoreRequest;
use App\Models\SchoolDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SchoolDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schoolId = (int) getSchool(auth()->user())->id;
        $scope = $this->scope($request);

        $documents = SchoolDocument::query()
            ->with('uploader:id,name')
            ->where('school_id', $schoolId)
            ->where('scope', $scope)
            ->latest()
            ->get()
            ->map(fn (SchoolDocument $document): array => $this->resource($document));

        return response()->json(['data' => $documents]);
    }

    public function store(SchoolDocumentStoreRequest $request): JsonResponse
    {
        $school = getSchool(auth()->user());
        $scope = $this->scope($request);
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $folder = $scope === SchoolDocument::SCOPE_CLUB ? 'club' : 'planning';
        $path = sprintf('%s/documents/%s/%s.%s', $school->slug, $folder, Str::uuid(), $extension);

        if (! Storage::disk('local')->putFileAs(dirname($path), $file, basename($path))) {
            return response()->json(['message' => 'No fue posible guardar el archivo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $document = DB::transaction(fn (): SchoolDocument => SchoolDocument::query()->create([
                'school_id' => $school->id,
                'uploaded_by' => auth()->id(),
                'scope' => $scope,
                'title' => $request->string('title')->trim()->toString(),
                'description' => $request->filled('description') ? $request->string('description')->trim()->toString() : null,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'extension' => $extension,
                'size_bytes' => $file->getSize(),
            ]));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $document->load('uploader:id,name');

        return response()->json(['data' => $this->resource($document)], Response::HTTP_CREATED);
    }

    public function download(Request $request, int $schoolDocument): BinaryFileResponse
    {
        $document = $this->findScoped($request, $schoolDocument);
        abort_unless(Storage::disk($document->disk)->exists($document->path), Response::HTTP_NOT_FOUND);

        return response()->download(
            Storage::disk($document->disk)->path($document->path),
            $document->original_name,
            ['Content-Type' => $document->mime_type]
        );
    }

    public function destroy(Request $request, int $schoolDocument): JsonResponse
    {
        $document = $this->findScoped($request, $schoolDocument);
        $disk = Storage::disk($document->disk);

        if ($disk->exists($document->path) && ! $disk->delete($document->path)) {
            return response()->json(['message' => 'No fue posible eliminar el archivo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $document->delete();

        return response()->json(['message' => 'Documento eliminado.']);
    }

    private function findScoped(Request $request, int $id): SchoolDocument
    {
        return SchoolDocument::query()
            ->where('school_id', (int) getSchool(auth()->user())->id)
            ->where('scope', $this->scope($request))
            ->findOrFail($id);
    }

    private function scope(Request $request): string
    {
        return str_contains((string) $request->route()?->getName(), '.club-documents.')
            ? SchoolDocument::SCOPE_CLUB
            : SchoolDocument::SCOPE_PLANNING;
    }

    private function resource(SchoolDocument $document): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'description' => $document->description,
            'original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'extension' => $document->extension,
            'size_bytes' => $document->size_bytes,
            'uploader_name' => $document->uploader?->name,
            'created_at' => $document->created_at?->toIso8601String(),
        ];
    }
}
