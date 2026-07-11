<?php

namespace App\Http\Controllers;

use App\Service\Import\ImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;
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
        try {
            $request->validate([
                'file' => ['required', 'file'],
                'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            ]);

            $schoolId = $this->resolveImportSchoolId($request);

            $summary = $this->service->players($request->file('file'), $schoolId);

            if ($this->expectsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.player_created'),
                    'summary' => $summary,
                ]);
            }

            Alert::success(env('APP_NAME'), __('messages.player_created'));

        } catch (ValidationException $exception) {
            if ($this->expectsJsonResponse($request)) {
                $message = $exception->validator->errors()->first('file');
                if (str_starts_with($message, 'Error en las columnas:')) {
                    return response()->json(['success' => false, 'message' => $message], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                throw $exception;
            }

            Alert::error(env('APP_NAME'), $exception->validator->errors()->first());
        } catch (Throwable $th) {
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
}
