<?php

namespace App\Http\Controllers;

use App\Imports\ImportMatchDetail;
use App\Imports\ImportPlayers;
use App\Repositories\AssistRepository;
use App\Repositories\GameRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class ImportController extends Controller
{
    public function __construct(
        private InscriptionRepository $inscriptionRepository,
        private AssistRepository $assistRepository,
        private IncidentRepository $incidentRepository,
        private GameRepository $gameRepository,
        private PlayerRepository $playerRepository
    ) {}

    public function importMatchDetail(Request $request)
    {
        $file = $request->file('file');

        $importMatchDetail = new ImportMatchDetail($request->input('match', null));

        Excel::import($importMatchDetail, $file);

        $response = $this->gameRepository->loadDataFromFile($importMatchDetail->getData());

        return response()->json($response);

    }

    public function importPlayers(Request $request, PlayerRepository $playerRepository)
    {
        try {
            $request->validate([
                'file' => ['required', 'file'],
                'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            ]);

            $schoolId = $this->resolveImportSchoolId($request);

            $diff = $this->playerRepository->validateImport($request->file('file'));
            if ($diff !== '') {
                $message = "Error en las columnas: {$diff}";

                if ($this->expectsJsonResponse($request)) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                Alert::error('Error en las columnas a importar', $message);

                return back();
            }

            $importPlayers = new ImportPlayers($schoolId, $playerRepository, $this->inscriptionRepository);
            Excel::import($importPlayers, $request->file('file'));

            if ($this->expectsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.player_created'),
                ]);
            }

            Alert::success(env('APP_NAME'), __('messages.player_created'));

        } catch (ValidationException $exception) {
            if ($this->expectsJsonResponse($request)) {
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
