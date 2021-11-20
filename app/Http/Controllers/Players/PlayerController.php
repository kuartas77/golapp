<?php

namespace App\Http\Controllers\Players;

use App\Models\Player;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Repositories\PlayerRepository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use App\Http\Requests\Player\PlayerCreateRequest;
use App\Http\Requests\Player\PlayerUpdateRequest;

class PlayerController extends Controller
{
    /**
     * @var PlayerRepository
     */
    private PlayerRepository $repository;

    public function __construct(PlayerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('player.index');
    }

    /**
     * @return Application|Factory|View
     */
    public function create()
    {
        abort_unless(isAdmin(), 404);
        view()->share('edit', false);
        view()->share('peoples', collect([1, 2, 3]));
        return view('player.create');
    }

    /**
     * @param PlayerCreateRequest $request
     * @return RedirectResponse
     */
    public function store(PlayerCreateRequest $request): RedirectResponse
    {
        abort_unless(isAdmin(), 404);
        $player = $this->repository->createPlayer($request);
        if (!$player->wasRecentlyCreated) {
            alert()->error(env('APP_NAME'), __('messages.error_general'));
            return back()->withInput($request->input());
        }

        alert()->success(env('APP_NAME'), __('messages.player_created'));
        return redirect()->to(route('players.index'));
    }

    /**
     * @param Player $player
     * @return Application|Factory|View
     */
    public function show(Player $player)
    {
        $player = $this->repository->loadShow($player);
        view()->share('player', $player);
        return view('player.show');
    }

    /**
     * @param Player $player
     * @return Application|Factory|View
     */
    public function edit(Player $player)
    {
        abort_unless(isAdmin(), 404);
        $player->load('peoples');
        view()->share('edit', true);
        view()->share('player', $player);
        return view('player.edit');
    }

    /**
     * @param PlayerUpdateRequest $request
     * @param Player $player
     * @return RedirectResponse
     */
    public function update(PlayerUpdateRequest $request, Player $player): RedirectResponse
    {
        abort_unless(isAdmin(), 404);
        $player = $this->repository->updatePlayer($player, $request);
        if (is_null($player)) {
            alert()->error(env('APP_NAME'), __('messages.error_general'));
            return back()->withInput($request->input());
        }

        alert()->success(env('APP_NAME'), __('messages.player_updated'));
        return redirect()->to(route('players.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        abort(401);
    }
}
