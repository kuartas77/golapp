<?php

namespace App\Repositories;

use App\Models\Player;
use App\Models\UniformRequest;
use App\Traits\ErrorTrait;
use Illuminate\Support\Facades\DB;

class UniformRequestRepository
{
    use ErrorTrait;

    public function uniformRequestPlayer()
    {
        $player = request()->user();
        $player->load(['uniform_requests']);
        return $player->uniform_requests;
    }

    public function queryTable()
    {
        $generalQuery = UniformRequest::query()
        ->select([
            'uniform_request.*',
            'inscriptions.id as inscription_id',
            DB::raw("concat(players.names, ' ', players.last_names) AS full_names")
        ])
        ->join('players', 'uniform_request.player_id', '=', 'players.id')
        ->join('inscriptions', function($join) {
            $join->on('players.id', '=', 'inscriptions.player_id')
            ->where('year', now()->year)
            ->whereNull('inscriptions.deleted_at');
        })
        ->where('status', '<>', 'APPROVED')
        ->schoolId();

        return datatables()->of($generalQuery)
        ->filterColumn('created_at', fn($query, $keyword) => $query->whereDate('uniform_request.created_at', $keyword))
        ->filterColumn('full_names', function($query, $keyword) {
            $sql = "CONCAT(players.names, ' ', players.last_names) like ?";
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->toJson();
    }

    public function store(array $validated): array|UniformRequest
    {
        $model = [];
        try {
            $player = request()->user();

            $uniformRequest = new UniformRequest();
            $uniformRequest->school_id = $player->school_id;
            $uniformRequest->player_id = $player->id;
            $uniformRequest->type = $validated['type'];
            $uniformRequest->quantity = $validated['quantity'];
            $uniformRequest->size = $validated['size'];
            $uniformRequest->additional_notes = $validated['additional_notes'];

            DB::beginTransaction();
            $uniformRequest->save();
            DB::commit();
            $model = $uniformRequest;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError('UniformRequestRepository store failed', $th, [
                'type' => $validated['type'] ?? null,
                'player_id' => $player->id ?? null,
            ]);
            $model = [];
        }

        return $model;
    }

    public function cancel(UniformRequest $uniformRequest): bool
    {
        $success = true;
        try {
            $uniformRequest->status = 'CANCELLED';
            if($uniformRequest->additional_notes) {
                $uniformRequest->additional_notes = $uniformRequest->additional_notes . " Cancelada por el usuario.";
            }else {
                $uniformRequest->additional_notes = "Cancelada por el usuario.";
            }
            DB::beginTransaction();
            $uniformRequest->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError('UniformRequestRepository cancel failed', $th, [
                'uniform_request_id' => $uniformRequest->id ?? null,
            ]);
            $success = false;
        }
        return $success;
    }

    public function findPlayerRequestOrFail(int $uniformRequestId): UniformRequest
    {
        /** @var Player $player */
        $player = request()->user();

        return $player->uniform_requests()
            ->whereKey($uniformRequestId)
            ->firstOrFail();
    }

}
