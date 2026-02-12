<?php

namespace App\Http\Controllers\Portal;

use App\Repositories\PlayerRepository;
use App\Http\Requests\Portal\PlayerPortalUpdateRequest;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class HomePlayerController extends Controller
{
    public function index()
    {
        return redirect(route('portal.player.show', [auth()->user()->unique_code]));
    }

    public function show($uniqueCode, PlayerRepository $playerRepository)
    {

        $player = $playerRepository->loadShow(auth()->user());
        $inscriptionActive = $player->inscriptions->firstWhere('year', now()->year);
        view()->share('player', $player);
        view()->share('school', $player->schoolData);
        view()->share('inscription', $inscriptionActive);
        return view('portal.players.show');
    }

    public function update(PlayerPortalUpdateRequest $request, string $unique_code = null, PlayerRepository $playerRepository)
    {
        $saved = $playerRepository->updatePlayerPortal(auth()->user(), $request);
        if (is_null($saved)) {
            Alert::error(env('APP_NAME'), __('messages.error_general'));
            return back()->withInput($request->input());
        }

        Alert::success(env('APP_NAME'), __('messages.player_updated'));
        return redirect(route('portal.player.show', [auth()->user()->unique_code]));
    }
}
