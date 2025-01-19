<?php

namespace App\Http\Controllers\Portal;

use App\Repositories\PlayerRepository;
use App\Http\Requests\Player\PlayerUpdateRequest;
use App\Http\Controllers\Controller;

class HomePlayerController extends Controller
{
    public function index()
    {
        return redirect(route('portal.player.show', [auth()->user()->unique_code]));
    }

    public function show($uniqueCode, PlayerRepository $playerRepository)
    {

        $player = $playerRepository->loadShow(auth()->user());

        view()->share('player', $player);
        view()->share('school', $player->schoolData);
        return view('portal.players.show');
    }

    public function update(PlayerUpdateRequest $request, string $unique_code = null, PlayerRepository $playerRepository)
    {
        $saved = $playerRepository->updatePlayerPortal(auth()->user(), $request->validated());
        if (is_null($saved)) {
            alert()->error(env('APP_NAME'), __('messages.error_general'));
            return back()->withInput($request->input());
        }

        alert()->success(env('APP_NAME'), __('messages.player_updated'));
        return redirect(route('portal.player.show', [auth()->user()->unique_code]));
    }
}
