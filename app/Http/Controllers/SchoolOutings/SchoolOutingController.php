<?php

declare(strict_types=1);

namespace App\Http\Controllers\SchoolOutings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolOutings\SchoolOutingActivityRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingContributionRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingParticipantRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingStatusRequest;
use App\Models\SchoolOuting;
use App\Models\SchoolOutingActivity;
use App\Models\SchoolOutingParticipant;
use App\Service\SchoolOutings\SchoolOutingQueryService;
use App\Service\SchoolOutings\SchoolOutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SchoolOutingController extends Controller
{
    public function __construct(
        private SchoolOutingService $service,
        private SchoolOutingQueryService $queries,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->queries->list($this->schoolId())]);
    }

    public function store(SchoolOutingRequest $request): JsonResponse
    {
        $outing = $this->service->create($request->validated(), $this->schoolId(), (int) auth()->id());
        return response()->json(['message' => 'Salida creada correctamente.', 'data' => $this->queries->load($outing)], Response::HTTP_CREATED);
    }

    public function show(SchoolOuting $outing): JsonResponse
    {
        $this->service->authorizeOuting($outing, $this->schoolId());
        return response()->json(['data' => $this->queries->load($outing)]);
    }

    public function update(SchoolOutingRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->service->update($outing, $request->validated(), $this->schoolId());
        return response()->json(['message' => 'Salida actualizada correctamente.', 'data' => $this->queries->load($outing)]);
    }

    public function updateStatus(SchoolOutingStatusRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->service->updateStatus($outing, $request->validated('status'), $this->schoolId());
        return response()->json(['message' => 'Estado de la salida actualizado correctamente.', 'data' => $this->queries->load($outing)]);
    }

    public function eligibleInscriptions(Request $request, SchoolOuting $outing): JsonResponse
    {
        $schoolId = $this->schoolId();
        $this->service->authorizeOuting($outing, $schoolId);
        return response()->json(['data' => $this->queries->eligibleInscriptions($schoolId, $outing, [
            'training_group_id' => $request->filled('training_group_id') ? $request->integer('training_group_id') : null,
            'category' => $request->filled('category') ? $request->input('category') : null,
            'search' => $request->filled('search') ? trim((string) $request->input('search')) : null,
        ])]);
    }

    public function addParticipants(SchoolOutingParticipantRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->service->addParticipants($outing, $request->validated('inscription_ids'), $this->schoolId());
        return response()->json(['message' => 'Deportistas agregados correctamente.', 'data' => $this->queries->load($outing)], Response::HTTP_CREATED);
    }

    public function removeParticipant(SchoolOuting $outing, SchoolOutingParticipant $participant): JsonResponse
    {
        $this->service->removeParticipant($outing, $participant, $this->schoolId());
        return response()->json(['message' => 'Deportista retirado de la salida correctamente.', 'data' => $this->queries->load($outing)]);
    }

    public function storeActivity(SchoolOutingActivityRequest $request, SchoolOuting $outing): JsonResponse
    {
        $activity = $this->service->createActivity($outing, $request->validated(), $this->schoolId());
        return response()->json(['message' => 'Actividad creada correctamente.', 'data' => $activity], Response::HTTP_CREATED);
    }

    public function updateActivity(SchoolOutingActivityRequest $request, SchoolOuting $outing, SchoolOutingActivity $activity): JsonResponse
    {
        $activity = $this->service->updateActivity($outing, $activity, $request->validated(), $this->schoolId());
        return response()->json(['message' => 'Actividad actualizada correctamente.', 'data' => $activity]);
    }

    public function destroyActivity(SchoolOuting $outing, SchoolOutingActivity $activity): JsonResponse
    {
        $this->service->deleteActivity($outing, $activity, $this->schoolId());
        return response()->json(['message' => 'Actividad eliminada correctamente.', 'data' => $this->queries->load($outing)]);
    }

    public function storeContribution(SchoolOutingContributionRequest $request, SchoolOuting $outing): JsonResponse
    {
        $contribution = $this->service->createContribution($outing, $request->validated(), $this->schoolId(), (int) auth()->id());
        return response()->json([
            'message' => 'Abono registrado correctamente.',
            'data' => $contribution->load(['participant' => fn ($query) => $query->with('player')->withSum('contributions', 'amount'), 'activity']),
            'outing' => $this->queries->load($outing),
        ], Response::HTTP_CREATED);
    }

    private function schoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }
}
