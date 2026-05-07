<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TournamentCreateRequest;
use App\Http\Requests\TournamentUpdateRequest;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TournamentController extends Controller
{
    public function index(): JsonResponse
    {
        $tournaments = Tournament::query()
            ->schoolId()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($tournaments);
    }

    public function show(int $id): JsonResponse
    {
        return $this->responseJson($this->findTournament($id));
    }

    public function store(TournamentCreateRequest $request): JsonResponse
    {
        $schoolId = getSchool(auth()->user())->id;
        $validated = $request->validated();

        $existing = Tournament::withTrashed()
            ->where('school_id', $schoolId)
            ->firstWhere('name', $validated['name']);

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                $message = 'Torneo reactivado correctamente.';
            } else {
                $message = 'El torneo ya existía.';
            }

            Cache::forget("KEY_TOURNAMENT_{$schoolId}");

            return response()->json([
                'message' => $message,
                'data' => $existing->fresh(['competitionGroup']),
            ]);
        }

        return $this->persistTournament(
            new Tournament(),
            $request,
            'Torneo creado correctamente.',
            201
        );
    }

    public function update(int $id, TournamentUpdateRequest $request): JsonResponse
    {
        return $this->persistTournament(
            $this->findTournament($id),
            $request,
            'Torneo actualizado correctamente.'
        );
    }

    public function destroy(int $id): Response
    {
        $tournament = $this->findTournament($id);
        $schoolId = $tournament->school_id;

        $tournament->delete();

        Cache::forget("KEY_TOURNAMENT_{$schoolId}");

        return response()->noContent();
    }

    private function persistTournament(
        Tournament $tournament,
        TournamentCreateRequest|TournamentUpdateRequest $request,
        string $message,
        int $status = 200
    ): JsonResponse {
        try {
            $tournament->fill($request->validated());
            $tournament->school_id = getSchool(auth()->user())->id;
            $tournament->save();

            Cache::forget("KEY_TOURNAMENT_{$tournament->school_id}");

            return response()->json([
                'message' => $message,
                'data' => $tournament->fresh(),
            ], $status);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible guardar el torneo.',
            ], 500);
        }
    }

    private function findTournament(int $id): Tournament
    {
        return Tournament::query()
            ->schoolId()
            ->findOrFail($id);
    }
}
