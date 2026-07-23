<?php

declare(strict_types=1);

namespace App\Service\Portal;

use App\Http\Resources\API\Portal\GuardianPlayerDetailResource;
use App\Models\People;
use App\Models\Player;
use App\Service\Player\PlayerExportService;
use Illuminate\Http\Request;

class GuardianPlayerExperienceService
{
    public function __construct(private GuardianAccessService $guardianAccessService)
    {
    }

    public function portalDetailPayload(People $guardian, int $playerId, Request $request): array
    {
        return (new GuardianPlayerDetailResource(
            $this->loadPlayerDetail($this->guardianAccessService->findEligiblePlayer($guardian, $playerId))
        ))->resolve($request);
    }

    public function sportsSummaryPayload(People $guardian, int $playerId, Request $request): array
    {
        $detail = $this->portalDetailPayload($guardian, $playerId, $request);
        $inscription = $detail['current_inscription'] ?? null;

        return [
            'id' => $detail['id'] ?? null,
            'unique_code' => $detail['unique_code'] ?? null,
            'full_names' => $detail['full_names'] ?? null,
            'photo_url' => $detail['photo_url'] ?? null,
            'current_inscription' => $inscription ? [
                'id' => $inscription['id'],
                'year' => $inscription['year'],
                'category' => $inscription['category'],
                'training_group' => $inscription['training_group'],
                'complementary_group' => $inscription['complementary_group'],
                'stats' => $inscription['stats'],
            ] : null,
        ];
    }

    public function activityPayload(People $guardian, int $playerId, Request $request): array
    {
        $detail = $this->portalDetailPayload($guardian, $playerId, $request);
        $inscription = $detail['current_inscription'] ?? null;

        return [
            'player' => [
                'id' => $detail['id'] ?? null,
                'unique_code' => $detail['unique_code'] ?? null,
                'full_names' => $detail['full_names'] ?? null,
                'photo_url' => $detail['photo_url'] ?? null,
            ],
            'current_inscription' => $inscription ? [
                'id' => $inscription['id'],
                'year' => $inscription['year'],
                'category' => $inscription['category'],
            ] : null,
            'payments' => $inscription['payments'] ?? [],
            'attendance' => $inscription['attendance'] ?? [],
        ];
    }

    public function loadPlayerDetail(Player $player): Player
    {
        $player->load([
            'schoolData',
            'inscriptions' => fn ($query) => $query
                ->where('year', now()->year)
                ->with([
                    'trainingGroup' => fn ($trainingQuery) => $trainingQuery->withTrashed(),
                    'complementaryGroup' => fn ($trainingQuery) => $trainingQuery->withTrashed(),
                    'payments',
                    'assistance' => fn ($assistQuery) => $assistQuery
                        ->with(['trainingGroup' => fn ($groupQuery) => $groupQuery->withTrashed()])
                        ->orderBy('month')
                        ->orderBy('training_group_id'),
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
