<?php

namespace App\Http\Controllers\Tournaments;

use Illuminate\View\View;
use App\Models\Tournament;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\TournamentCreateRequest;
use App\Http\Requests\TournamentUpdateRequest;
use Illuminate\Contracts\Foundation\Application;

class TournamentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|Factory|JsonResponse|View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()->collection(Tournament::schoolId()->get())->toJson();
        }
        return view('tournaments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TournamentCreateRequest $request
     * @return RedirectResponse
     */
    public function store(TournamentCreateRequest $request): RedirectResponse
    {
        $exist = Tournament::withTrashed()->firstWhere('name', $request->input('name'));

        if ($exist) {
            $exist->trashed() == false ?: $exist->restore();
            alert()->info(env('APP_NAME'), __('messages.tournament_exists'));
            Cache::forget("KEY_TOURNAMENT_{$request->input('school_id')}");
            return back();
        }
        $tournament = Tournament::create($request->validated());
        if ($tournament->wasRecentlyCreated) {
            alert()->success(env('APP_NAME'), __('messages.tournament_stored'));
            Cache::forget("KEY_TOURNAMENT_{$request->input('school_id')}");
        } else {
            alert()->error(env('APP_NAME'), __('match_fail'));
        }
        return back();

    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Tournament $tournament
     * @return JsonResponse
     */
    public function show(Request $request, Tournament $tournament): JsonResponse
    {
        return $request->ajax() ? response()->json($tournament) : response()->json([], 404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit()
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TournamentUpdateRequest $request
     * @param Tournament $tournament
     * @return RedirectResponse
     */
    public function update(TournamentUpdateRequest $request, Tournament $tournament): RedirectResponse
    {
        if ($tournament->update($request->validated())) {
            alert()->success(env('APP_NAME'), __('messages.tournament_updated'));
            Cache::forget("KEY_TOURNAMENT_{$request->input('school_id')}");
        } else {
            alert()->error(env('APP_NAME'), __('match_fail'));
        }
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy()
    {
        abort(404);
    }
}
