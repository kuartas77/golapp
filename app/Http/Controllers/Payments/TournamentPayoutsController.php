<?php

namespace App\Http\Controllers\Payments;

use Illuminate\Http\Request;
use App\Models\TournamentPayout;
use App\Http\Controllers\Controller;
use App\Repositories\TournamentPayoutsRepository;
use App\Http\Requests\SetTournamentPaymentRequest;
use App\Http\Resources\API\TournamentPays\TournamentPaymentCollection;

class TournamentPayoutsController extends Controller
{
    private TournamentPayoutsRepository $repository;

    public function __construct(TournamentPayoutsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        if(($request->ajax())){
            return $this->repository->search($request->only(['tournament_id', 'competition_group_id', 'unique_code']));
        }elseif($request->wantsJson()){
            $data = $this->repository->search($request->only(['tournament_id', 'competition_group_id', 'unique_code']), true);
            return new TournamentPaymentCollection($data);
        }

        return view('payments.tournaments.index');
    }

    public function store(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json($this->repository->create($request->only(['tournament_id', 'competition_group_id'])));
    }

    public function update(SetTournamentPaymentRequest $request, TournamentPayout $tournamentpayout)
    {
        abort_unless($request->ajax(), 401);
        $isPay = $this->repository->update($tournamentpayout, $request->validated());
        return $this->responseJson($isPay);
    }
}
