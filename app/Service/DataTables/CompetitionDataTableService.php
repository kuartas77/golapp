<?php

namespace App\Service\DataTables;

use App\Models\Game;
use App\Repositories\GameRepository;
use App\Service\InstructorPeriodEditPolicy;
use Illuminate\Http\JsonResponse;

class CompetitionDataTableService
{
    public function __construct(private GameRepository $games, private InstructorPeriodEditPolicy $periodPolicy) {}

    public function matches(): JsonResponse
    {
        return datatables()->of($this->games->getDatatable())
            ->filterColumn('status', fn ($query, $keyword) => $query->where('games.status', $keyword))
            ->editColumn('final_score', fn (Game $game) => $game->final_score_array)
            ->addColumn('status_label', fn (Game $game) => $game->status_label)
            ->addColumn('period_locked', fn (Game $game) => !$this->periodPolicy->canMutateDate($game->date))
            ->toJson();
    }
}
