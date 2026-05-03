<?php

namespace App\Http\Controllers\Portal;

use App\Models\People;
use App\Models\Player;
use App\Repositories\PlayerRepository;
use App\Service\Player\PlayerExportService;
use App\Service\Portal\GuardianAccessService;
use App\Http\Requests\Portal\PlayerPortalUpdateRequest;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomePlayerController extends Controller
{
    public function __construct(private GuardianAccessService $guardianAccessService)
    {
        //
    }

    public function index(Request $request): View
    {
        /** @var People $guardian */
        $guardian = $request->user();

        $players = $this->guardianAccessService->eligiblePlayersQuery($guardian)
            ->with([
                'schoolData',
                'inscriptions' => fn ($query) => $query
                    ->where('year', now()->year)
                    ->with(['trainingGroup' => fn ($trainingQuery) => $trainingQuery->withTrashed()]),
            ])
            ->orderBy('players.names')
            ->orderBy('players.last_names')
            ->get();

        return view('portal.players.home', compact('guardian', 'players'));
    }

    public function show(Request $request, Player $player, PlayerRepository $playerRepository): View
    {
        /** @var People $guardian */
        $guardian = $request->user();

        $player = $playerRepository->loadShow($this->resolveGuardianPlayer($guardian, $player->id));
        $inscriptionActive = $player->inscriptions->firstWhere('year', now()->year) ?? $player->inscriptions->first();

        return view('portal.players.show', [
            'guardian' => $guardian,
            'player' => $player,
            'school' => $player->schoolData,
            'inscription' => $inscriptionActive,
        ]);
    }

    public function update(
        PlayerPortalUpdateRequest $request,
        Player $player,
        PlayerRepository $playerRepository
    ): RedirectResponse {
        /** @var People $guardian */
        $guardian = $request->user();

        $player = $this->resolveGuardianPlayer($guardian, $player->id);
        $saved = $playerRepository->updatePlayerPortal($player, $request);

        if (!$saved) {
            Alert::error(env('APP_NAME'), __('messages.error_general'));
            return back()->withInput($request->input());
        }

        Alert::success(env('APP_NAME'), __('messages.player_updated'));

        return redirect(route('portal.guardians.players.show', [$player]));
    }

    public function inscriptionReport(
        Request $request,
        Player $player,
        int $inscription,
        PlayerExportService $playerExportService
    ) {
        /** @var People $guardian */
        $guardian = $request->user();

        $player = $this->resolveGuardianPlayer($guardian, $player->id);
        $inscriptionModel = $player->inscriptions()
            ->when(
                $inscription,
                fn ($query) => $query->whereKey($inscription),
                fn ($query) => $query->where('year', now()->year)
            )
            ->firstOrFail();

        return $playerExportService->makePDFInscriptionDetail($player->id, $inscriptionModel->id, (string) $inscriptionModel->year);
    }

    private function resolveGuardianPlayer(People $guardian, int $playerId): Player
    {
        return $this->guardianAccessService->findEligiblePlayer($guardian, $playerId);
    }
}
