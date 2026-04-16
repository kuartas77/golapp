<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Portal\GuardianPlayerUpdateRequest;
use App\Http\Resources\API\Portal\GuardianPlayerDetailResource;
use App\Http\Resources\API\Portal\GuardianPlayerListResource;
use App\Models\People;
use App\Models\Player;
use App\Repositories\PlayerRepository;
use App\Service\Evaluations\PlayerEvaluationComparisonService;
use App\Service\Player\PlayerExportService;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuardianPlayerController extends Controller
{
    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private PlayerEvaluationComparisonService $comparisonService
    ) {
    }

    public function index(Request $request): JsonResponse
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

        return response()->json([
            'data' => GuardianPlayerListResource::collection($players)->resolve(),
        ]);
    }

    public function show(Request $request, int $player): JsonResponse
    {
        /** @var People $guardian */
        $guardian = $request->user();

        $playerModel = $this->guardianAccessService->findEligiblePlayer($guardian, $player);
        $playerModel = $this->loadPlayerDetail($playerModel);

        return response()->json([
            'data' => (new GuardianPlayerDetailResource($playerModel))->resolve(),
        ]);
    }

    public function update(GuardianPlayerUpdateRequest $request, int $player, PlayerRepository $playerRepository): JsonResponse
    {
        /** @var People $guardian */
        $guardian = $request->user();

        $playerModel = $this->guardianAccessService->findEligiblePlayer($guardian, $player);
        $saved = $playerRepository->updatePlayerPortal($playerModel, $request);

        if (!$saved) {
            return response()->json([
                'message' => 'No fue posible actualizar los datos del deportista.',
            ], 500);
        }

        return response()->json([
            'message' => 'Datos del deportista actualizados correctamente.',
            'data' => (new GuardianPlayerDetailResource($this->loadPlayerDetail($playerModel->refresh())))->resolve(),
        ]);
    }

    public function inscriptionReport(Request $request, PlayerExportService $playerExportService, int $player, ?int $inscription = null)
    {
        /** @var People $guardian */
        $guardian = $request->user();

        $playerModel = $this->guardianAccessService->findEligiblePlayer($guardian, $player);
        $inscriptionModel = $playerModel->inscriptions()
            ->when(
                $inscription,
                fn ($query) => $query->whereKey($inscription),
                fn ($query) => $query->where('year', now()->year)
            )
            ->firstOrFail();

        return $playerExportService->makePDFInscriptionDetail(
            player_id: $playerModel->id,
            inscription_id: $inscriptionModel->id,
            year: (string) $inscriptionModel->year
        );
    }

    public function comparison(Request $request, int $inscription): JsonResponse
    {
        /** @var People $guardian */
        $guardian = $request->user();

        $validated = Validator::make($request->all(), [
            'period_a_id' => ['required', 'integer', 'exists:evaluation_periods,id'],
            'period_b_id' => ['required', 'integer', 'exists:evaluation_periods,id', 'different:period_a_id'],
        ])->validate();

        $inscriptionModel = $this->guardianAccessService->findEligibleInscription($guardian, $inscription);
        $inscriptionModel->loadMissing(['player', 'trainingGroup']);

        return response()->json([
            'data' => $this->comparisonService->compare(
                inscription: $inscriptionModel,
                periodAId: (int) $validated['period_a_id'],
                periodBId: (int) $validated['period_b_id']
            ),
        ]);
    }

    private function loadPlayerDetail(Player $player): Player
    {
        $player->load([
            'schoolData',
            'inscriptions' => fn ($query) => $query
                ->where('year', now()->year)
                ->with([
                    'school',
                    'trainingGroup' => fn ($trainingQuery) => $trainingQuery->withTrashed(),
                    'payments',
                    'assistance' => fn ($assistQuery) => $assistQuery->orderBy('month'),
                    'skillsControls',
                    'playerEvaluations.period',
                ]),
        ]);

        $player->historical_inscriptions = $player->inscriptions()
            ->select(['id', 'player_id', 'year'])
            ->where('year', '<', now()->year)
            ->orderByDesc('year')
            ->get();

        $player->inscriptions->setAppends(['format_average']);
        PlayerExportService::loadClassDays($player);

        return $player;
    }
}
