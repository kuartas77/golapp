<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Portal\GuardianPlayerUpdateRequest;
use App\Http\Resources\API\Portal\GuardianPlayerDetailResource;
use App\Http\Resources\API\Portal\GuardianPlayerListResource;
use App\Models\People;
use App\Repositories\PlayerRepository;
use App\Service\Evaluations\PlayerEvaluationComparisonService;
use App\Service\Player\PlayerExportService;
use App\Service\Portal\GuardianAccessService;
use App\Service\Portal\GuardianPlayerExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuardianPlayerController extends Controller
{
    public function __construct(
        private GuardianAccessService $guardianAccessService,
        private GuardianPlayerExperienceService $experienceService,
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

        return response()->json([
            'data' => $this->experienceService->portalDetailPayload($guardian, $player, $request),
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

        $resource = $this->experienceService->loadPlayerDetail($playerModel->refresh());
        $guardianPlayerDetail = new GuardianPlayerDetailResource($resource);

        return response()->json([
            'message' => 'Datos del deportista actualizados correctamente.',
            'data' => $guardianPlayerDetail->resolve($request),
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

}
