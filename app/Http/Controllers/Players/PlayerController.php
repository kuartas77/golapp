<?php

namespace App\Http\Controllers\Players;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\PlayerCreateRequest;
use App\Http\Requests\Player\PlayerUpdateRequest;
use App\Models\Player;
use App\Repositories\PlayerRepository;
use App\Service\Player\PlayerFinancialClearanceService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class PlayerController extends Controller
{
    public function __construct(
        private PlayerRepository $repository,
        private PlayerFinancialClearanceService $financialClearanceService
    ) {}

    public function index(): Factory|View|Application
    {
        $admin = isAdmin() || isSchool() ? 1 : 0;

        return view('player.index', compact('admin'));
    }

    public function create(): Factory|View|Application
    {
        abort_unless(isAdmin() || isSchool(), 404);
        view()->share('edit', false);
        view()->share('peoples', collect([1]));

        return view('player.create');
    }

    public function store(PlayerCreateRequest $request): RedirectResponse
    {
        abort_unless(isAdmin() || isSchool(), 404);
        $player = $this->repository->createPlayer($request);

        if ($player) {
            Alert::success(env('APP_NAME'), __('messages.player_created'));

            return redirect()->to('/players');
        }

        Alert::error(env('APP_NAME'), __('messages.error_general'));

        return back()->withInput($request->input());
    }

    /**
     * @param  Player  $player
     */
    public function show($uniqueCode): JsonResponse
    {
        $player = Player::where('unique_code', $uniqueCode)->where('school_id', getSchool(auth()->user())->id)->first();
        $player = $this->repository->loadShow($player);

        return response()->json($player);
    }

    /**
     * @param  Player  $player
     */
    public function edit($uniqueCode): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 404);
        $player = Player::with('people')->where('unique_code', $uniqueCode)->where('school_id', getSchool(auth()->user())->id)->first();
        $player->school_tutor_platform = (bool) getSchool(auth()->user())->tutor_platform;

        return response()->json($player);
    }

    /**
     * @param  Player  $player
     */
    public function update(PlayerUpdateRequest $request, $uniqueCode): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 404);
        $response = [];
        $player = Player::where('unique_code', $uniqueCode)->where('school_id', getSchool(auth()->user())->id)->first();
        $isUpdated = $this->repository->updatePlayer($player, $request);
        $response['success'] = $isUpdated ?: false;

        return response()->json($response);
    }

    public function financialClearanceStatus(string $uniqueCode): JsonResponse
    {
        $school = getSchool(auth()->user());
        $player = Player::query()
            ->where('school_id', $school->id)
            ->where('unique_code', $uniqueCode)
            ->firstOrFail();

        return response()->json($this->financialClearanceService->status($school, $player));
    }

    public function financialClearancePdf(string $uniqueCode)
    {
        $school = getSchool(auth()->user());
        $player = Player::query()
            ->where('school_id', $school->id)
            ->where('unique_code', $uniqueCode)
            ->firstOrFail();

        return $this->financialClearanceService->generatePdf($school, $player);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(): void
    {
        abort(401);
    }
}
