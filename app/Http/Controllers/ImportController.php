<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPlayerImport;
use App\Models\PlayerImport;
use App\Service\Import\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;
use RuntimeException;
use Throwable;

class ImportController extends Controller
{
    public function __construct(private ImportService $service) {}

    public function importMatchDetail(Request $request)
    {
        try {
            $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            ]);

            return response()->json($this->service->matchDetail($request->file('file')));
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->validator->errors()->first(),
                'errors' => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'success' => false,
                'message' => __('messages.error_general'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function importPlayers(Request $request)
    {
        $storedPath = null;
        $playerImport = null;

        try {
            $request->validate([
                'file' => ['required', 'file'],
                'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            ]);

            $schoolId = $this->resolveImportSchoolId($request);
            $file = $request->file('file');

            $this->service->validatePlayersFile($file);

            $activeImport = PlayerImport::query()
                ->where('school_id', $schoolId)
                ->whereIn('status', [PlayerImport::STATUS_PENDING, PlayerImport::STATUS_PROCESSING])
                ->latest('id')
                ->first();

            if ($activeImport) {
                $message = 'Ya hay una importación de deportistas en proceso para esta escuela.';

                if ($this->expectsJsonResponse($request)) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'import' => $activeImport->requested_by === $request->user()?->id
                            ? $this->playerImportPayload($activeImport)
                            : null,
                    ], Response::HTTP_CONFLICT);
                }

                Alert::info(env('APP_NAME'), $message);

                return back();
            }

            $uuid = (string) Str::uuid();
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'xlsx';
            $storedPath = $file->storeAs('player-imports', "{$uuid}.{$extension}", 'local');

            if (! $storedPath) {
                throw new RuntimeException('Player import file could not be stored.');
            }

            $playerImport = PlayerImport::query()->create([
                'uuid' => $uuid,
                'school_id' => $schoolId,
                'requested_by' => $request->user()?->id,
                'status' => PlayerImport::STATUS_PENDING,
                'disk' => 'local',
                'path' => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            ProcessPlayerImport::dispatch($playerImport->id);
            $playerImport->refresh();

            $message = 'La importación quedó en cola y se procesará en segundo plano.';

            if ($this->expectsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'import' => $this->playerImportPayload($playerImport),
                ], Response::HTTP_ACCEPTED);
            }

            Alert::success(env('APP_NAME'), $message);

        } catch (ValidationException $exception) {
            if ($this->expectsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->validator->errors()->first(),
                    'errors' => $exception->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Alert::error(env('APP_NAME'), $exception->validator->errors()->first());
        } catch (Throwable $th) {
            if ($playerImport?->fresh()?->status === PlayerImport::STATUS_PENDING) {
                $playerImport->delete();
            }
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            report($th);

            if ($this->expectsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_general'),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            Alert::error(env('APP_NAME'), __('messages.error_general'));
        }

        return back();
    }

    public function latestPlayerImport(Request $request): JsonResponse
    {
        $playerImport = PlayerImport::query()
            ->where('requested_by', $request->user()?->id)
            ->whereIn('status', [PlayerImport::STATUS_PENDING, PlayerImport::STATUS_PROCESSING])
            ->latest('id')
            ->first();

        return response()->json([
            'import' => $playerImport ? $this->playerImportPayload($playerImport) : null,
        ]);
    }

    public function playerImportStatus(Request $request, string $playerImportUuid): JsonResponse
    {
        $playerImport = PlayerImport::query()
            ->where('uuid', $playerImportUuid)
            ->where('requested_by', $request->user()?->id)
            ->firstOrFail();

        return response()->json([
            'import' => $this->playerImportPayload($playerImport),
        ]);
    }

    private function resolveImportSchoolId(Request $request): int
    {
        if (auth()->user()?->hasRole('super-admin')) {
            if (! $request->filled('school_id')) {
                throw ValidationException::withMessages([
                    'school_id' => 'Selecciona una escuela.',
                ]);
            }

            return (int) $request->input('school_id');
        }

        return (int) getSchool(auth()->user())->id;
    }

    private function expectsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->is('api/*');
    }

    private function playerImportPayload(PlayerImport $playerImport): array
    {
        return [
            'id' => $playerImport->uuid,
            'status' => $playerImport->status,
            'filename' => $playerImport->original_filename,
            'summary' => $playerImport->summary,
            'error_message' => $playerImport->error_message,
            'started_at' => optional($playerImport->started_at)->toIso8601String(),
            'completed_at' => optional($playerImport->completed_at)->toIso8601String(),
        ];
    }
}
