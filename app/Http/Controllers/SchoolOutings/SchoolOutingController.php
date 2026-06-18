<?php

declare(strict_types=1);

namespace App\Http\Controllers\SchoolOutings;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolOutings\SchoolOutingActivityRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingContributionRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingParticipantRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingStatusRequest;
use App\Models\Inscription;
use App\Models\SchoolOuting;
use App\Models\SchoolOutingActivity;
use App\Models\SchoolOutingContribution;
use App\Models\SchoolOutingParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SchoolOutingController extends Controller
{
    public function index(): JsonResponse
    {
        $outings = SchoolOuting::query()
            ->schoolId()
            ->withCount('participants')
            ->withSum('participants', 'target_amount')
            ->withSum('contributions', 'amount')
            ->latest('departure_date')
            ->latest('id')
            ->get();

        return response()->json(['data' => $outings]);
    }

    public function store(SchoolOutingRequest $request): JsonResponse
    {
        $outing = DB::transaction(function () use ($request): SchoolOuting {
            $outing = SchoolOuting::query()->create($request->validated() + [
                'school_id' => $this->schoolId(),
                'status' => SchoolOuting::STATUS_OPEN,
                'created_by' => auth()->id(),
            ]);

            $outing->activities()->create([
                'school_id' => $outing->school_id,
                'name' => 'Pago directo',
                'is_default' => true,
            ]);

            return $outing;
        });

        return response()->json([
            'message' => 'Salida creada correctamente.',
            'data' => $this->loadOuting($outing),
        ], Response::HTTP_CREATED);
    }

    public function show(SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);

        return response()->json(['data' => $this->loadOuting($outing)]);
    }

    public function update(SchoolOutingRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);
        $this->abortIfLocked($outing);

        $outing->update($request->validated());

        return response()->json([
            'message' => 'Salida actualizada correctamente.',
            'data' => $this->loadOuting($outing),
        ]);
    }

    public function updateStatus(SchoolOutingStatusRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);

        $status = $request->validated('status');

        if ($outing->isLocked() && $status !== $outing->status) {
            return response()->json([
                'message' => 'Las salidas cerradas o canceladas son de solo lectura.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $outing->update(['status' => $status]);

        return response()->json([
            'message' => 'Estado de la salida actualizado correctamente.',
            'data' => $this->loadOuting($outing),
        ]);
    }

    public function eligibleInscriptions(Request $request, SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);

        $schoolId = $this->schoolId();
        $assignedIds = $outing->participants()->pluck('inscription_id');

        $query = Inscription::query()
            ->select('inscriptions.*')
            ->with(['player:id,names,last_names,unique_code,category,photo,school_id', 'trainingGroup:id,name,category,school_id'])
            ->where('inscriptions.school_id', $schoolId)
            ->where('inscriptions.year', now()->year)
            ->whereNotIn('inscriptions.id', $assignedIds)
            ->whereHas('player')
            ->when($request->filled('training_group_id'), fn ($query) => $query->where('training_group_id', $request->integer('training_group_id')))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->whereHas('player', function ($playerQuery) use ($search): void {
                    $playerQuery->where('names', 'like', "%{$search}%")
                        ->orWhere('last_names', 'like', "%{$search}%")
                        ->orWhere('unique_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('category')
            ->orderBy('id')
            ->limit(100);

        return response()->json([
            'data' => $query->get()->map(fn (Inscription $inscription) => $this->serializeEligibleInscription($inscription))->values(),
        ]);
    }

    public function addParticipants(SchoolOutingParticipantRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);
        $this->abortIfLocked($outing);

        $schoolId = $this->schoolId();
        $inscriptions = Inscription::query()
            ->with('player')
            ->where('school_id', $schoolId)
            ->where('year', now()->year)
            ->whereIn('id', $request->validated('inscription_ids'))
            ->whereHas('player')
            ->get();

        DB::transaction(function () use ($outing, $inscriptions, $schoolId): void {
            foreach ($inscriptions as $inscription) {
                $participant = SchoolOutingParticipant::withTrashed()->firstOrNew([
                    'school_outing_id' => $outing->id,
                    'inscription_id' => $inscription->id,
                ]);

                $participant->fill([
                    'school_id' => $schoolId,
                    'player_id' => $inscription->player_id,
                    'target_amount' => $outing->amount_per_player,
                ]);

                if ($participant->trashed()) {
                    $participant->restore();
                }

                $participant->save();
            }
        });

        return response()->json([
            'message' => 'Deportistas agregados correctamente.',
            'data' => $this->loadOuting($outing),
        ], Response::HTTP_CREATED);
    }

    public function removeParticipant(SchoolOuting $outing, SchoolOutingParticipant $participant): JsonResponse
    {
        $this->authorizeOuting($outing);
        $this->authorizeParticipant($outing, $participant);
        $this->abortIfLocked($outing);

        if ($participant->contributions()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un deportista con abonos registrados.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $participant->delete();

        return response()->json([
            'message' => 'Deportista retirado de la salida correctamente.',
            'data' => $this->loadOuting($outing),
        ]);
    }

    public function storeActivity(SchoolOutingActivityRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);
        $this->abortIfLocked($outing);

        $activity = $outing->activities()->create($request->validated() + [
            'school_id' => $outing->school_id,
            'is_default' => false,
        ]);

        return response()->json([
            'message' => 'Actividad creada correctamente.',
            'data' => $activity,
        ], Response::HTTP_CREATED);
    }

    public function updateActivity(
        SchoolOutingActivityRequest $request,
        SchoolOuting $outing,
        SchoolOutingActivity $activity
    ): JsonResponse {
        $this->authorizeOuting($outing);
        $this->authorizeActivity($outing, $activity);
        $this->abortIfLocked($outing);

        $activity->update($request->validated());

        return response()->json([
            'message' => 'Actividad actualizada correctamente.',
            'data' => $activity->fresh(),
        ]);
    }

    public function destroyActivity(SchoolOuting $outing, SchoolOutingActivity $activity): JsonResponse
    {
        $this->authorizeOuting($outing);
        $this->authorizeActivity($outing, $activity);
        $this->abortIfLocked($outing);

        if ($activity->contributions()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una actividad con abonos registrados.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $activity->delete();

        return response()->json([
            'message' => 'Actividad eliminada correctamente.',
            'data' => $this->loadOuting($outing),
        ]);
    }

    public function storeContribution(SchoolOutingContributionRequest $request, SchoolOuting $outing): JsonResponse
    {
        $this->authorizeOuting($outing);
        $this->abortIfLocked($outing);

        $participant = SchoolOutingParticipant::query()->findOrFail($request->validated('school_outing_participant_id'));
        $activity = SchoolOutingActivity::query()->findOrFail($request->validated('school_outing_activity_id'));

        $this->authorizeParticipant($outing, $participant);
        $this->authorizeActivity($outing, $activity);

        $contribution = SchoolOutingContribution::query()->create($request->validated() + [
            'school_outing_id' => $outing->id,
            'school_id' => $outing->school_id,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Abono registrado correctamente.',
            'data' => $contribution->load([
                'participant' => fn ($query) => $query
                    ->with('player')
                    ->withSum('contributions', 'amount'),
                'activity',
            ]),
            'outing' => $this->loadOuting($outing),
        ], Response::HTTP_CREATED);
    }

    private function loadOuting(SchoolOuting $outing): SchoolOuting
    {
        return $outing->fresh()
            ->load([
                'activities' => fn ($query) => $query->orderByDesc('is_default')->orderBy('name'),
                'participants' => fn ($query) => $query
                    ->with(['player:id,names,last_names,unique_code,category,photo,school_id', 'inscription.trainingGroup:id,name,category,school_id'])
                    ->withSum('contributions', 'amount')
                    ->orderBy('id'),
                'contributions' => fn ($query) => $query
                    ->with([
                        'participant' => fn ($participantQuery) => $participantQuery
                            ->with('player:id,names,last_names,unique_code,school_id')
                            ->withSum('contributions', 'amount'),
                        'activity:id,name',
                    ])
                    ->latest('contribution_date')
                    ->latest('id'),
            ])
            ->loadCount('participants')
            ->loadSum('participants', 'target_amount')
            ->loadSum('contributions', 'amount');
    }

    private function serializeEligibleInscription(Inscription $inscription): array
    {
        return [
            'id' => $inscription->id,
            'player_id' => $inscription->player_id,
            'player_name' => $inscription->player?->full_names,
            'unique_code' => $inscription->player?->unique_code,
            'category' => $inscription->category,
            'training_group_id' => $inscription->training_group_id,
            'training_group_name' => $inscription->trainingGroup?->full_group ?? $inscription->trainingGroup?->name,
        ];
    }

    private function authorizeOuting(SchoolOuting $outing): void
    {
        abort_unless((int) $outing->school_id === $this->schoolId(), Response::HTTP_NOT_FOUND);
    }

    private function authorizeParticipant(SchoolOuting $outing, SchoolOutingParticipant $participant): void
    {
        abort_unless(
            (int) $participant->school_outing_id === (int) $outing->id
            && (int) $participant->school_id === $this->schoolId(),
            Response::HTTP_NOT_FOUND
        );
    }

    private function authorizeActivity(SchoolOuting $outing, SchoolOutingActivity $activity): void
    {
        abort_unless(
            (int) $activity->school_outing_id === (int) $outing->id
            && (int) $activity->school_id === $this->schoolId(),
            Response::HTTP_NOT_FOUND
        );
    }

    private function abortIfLocked(SchoolOuting $outing): void
    {
        abort_if($outing->isLocked(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Las salidas cerradas o canceladas son de solo lectura.');
    }

    private function schoolId(): int
    {
        return (int) getSchool(auth()->user())->id;
    }
}
