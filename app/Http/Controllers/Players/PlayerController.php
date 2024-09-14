<?php

namespace App\Http\Controllers\Players;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\PlayerCreateRequest;
use App\Http\Requests\Player\PlayerUpdateRequest;
use App\Models\Player;
use App\Repositories\PlayerRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function __construct(private PlayerRepository $repository)
    {
    }

    /**
     * @return Application|Factory|View
     */
    public function index(): Factory|View|Application
    {
        $admin = isAdmin() || isSchool() ? 1 : 0;
        return view('player.index', compact('admin'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create(): Factory|View|Application
    {
        abort_unless(isAdmin() || isSchool(), 404);
        view()->share('edit', false);
        view()->share('peoples', collect([1]));
        return view('player.create');
    }

    /**
     * @param PlayerCreateRequest $request
     * @return RedirectResponse
     */
    public function store(PlayerCreateRequest $request): RedirectResponse
    {
        abort_unless(isAdmin() || isSchool(), 404);
        $player = $this->repository->createPlayer($request);

        if($player){
            alert()->success(env('APP_NAME'), __('messages.player_created'));
            return redirect()->to(route('players.index'));
        }

        alert()->error(env('APP_NAME'), __('messages.error_general'));
        return back()->withInput($request->input());
    }

    /**
     * @param Player $player
     * @return Application|Factory|View
     */
    public function show(Player $player): Factory|View|Application
    {
        $player = $this->repository->loadShow($player);
        view()->share('player', $player);
        return view('player.show');
    }

    /**
     * @param Player $player
     * @return Application|Factory|View
     */
    public function edit(Player $player): Factory|View|Application
    {
        abort_unless(isAdmin() || isSchool(), 404);
        $player->load('people');
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
        abort_unless(isAdmin() || isSchool(), 404);
        $isUpdated = $this->repository->updatePlayer($player, $request);
        if (!$isUpdated) {
            alert()->error(env('APP_NAME'), __('messages.error_general'));
            return back()->withInput($request->input());
        }

        alert()->success(env('APP_NAME'), __('messages.player_updated'));
        return redirect()->to(route('players.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(): void
    {
        abort(401);
    }
}
