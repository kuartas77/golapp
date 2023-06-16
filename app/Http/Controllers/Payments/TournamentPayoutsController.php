<?php

namespace App\Http\Controllers\Payments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TournamentPayout;
use App\Repositories\TournamentPayoutsRepository;

class TournamentPayoutsController extends Controller
{
    private TournamentPayoutsRepository $repository;

    public function __construct(TournamentPayoutsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->repository->search($request->only(['tournament_id', 'competition_group_id', 'unique_code']));
        }

        return view('payments.tournaments.index');
    }

    public function store(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json($this->repository->create($request->only(['tournament_id', 'competition_group_id'])));
    }

    public function update(Request $request, TournamentPayout $tournamentpayout)
    {
        abort_unless($request->ajax(), 401);
        $isPay = $this->repository->update($tournamentpayout, $request->only(['status']));
        return $this->responseJson($isPay);
    }
}
