<?php

declare(strict_types=1);

namespace App\Service\SchoolOutings;

use App\Models\Inscription;
use App\Models\SchoolOuting;
use App\Models\SchoolOutingActivity;
use App\Models\SchoolOutingContribution;
use App\Models\SchoolOutingParticipant;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SchoolOutingService
{
    public function create(array $data, int $schoolId, int $userId): SchoolOuting
    {
        return DB::transaction(function () use ($data, $schoolId, $userId): SchoolOuting {
            $outing = SchoolOuting::query()->create($data + [
                'school_id' => $schoolId,
                'status' => SchoolOuting::STATUS_OPEN,
                'created_by' => $userId,
            ]);
            $outing->activities()->create(['school_id' => $schoolId, 'name' => 'Pago directo', 'is_default' => true]);

            return $outing;
        });
    }

    public function update(SchoolOuting $outing, array $data, int $schoolId): SchoolOuting
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->assertUnlocked($outing);
        $outing->update($data);

        return $outing;
    }

    public function updateStatus(SchoolOuting $outing, string $status, int $schoolId): SchoolOuting
    {
        $this->authorizeOuting($outing, $schoolId);
        abort_if($outing->isLocked() && $status !== $outing->status, Response::HTTP_UNPROCESSABLE_ENTITY, 'Las salidas cerradas o canceladas son de solo lectura.');
        $outing->update(['status' => $status]);

        return $outing;
    }

    public function addParticipants(SchoolOuting $outing, array $inscriptionIds, int $schoolId): void
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->assertUnlocked($outing);
        $inscriptions = Inscription::query()->with('player')->where('school_id', $schoolId)->where('year', now()->year)->whereIn('id', $inscriptionIds)->whereHas('player')->get();

        DB::transaction(function () use ($outing, $inscriptions, $schoolId): void {
            foreach ($inscriptions as $inscription) {
                $participant = SchoolOutingParticipant::withTrashed()->firstOrNew(['school_outing_id' => $outing->id, 'inscription_id' => $inscription->id]);
                $participant->fill(['school_id' => $schoolId, 'player_id' => $inscription->player_id, 'target_amount' => $outing->amount_per_player]);
                if ($participant->trashed()) {
                    $participant->restore();
                }
                $participant->save();
            }
        });
    }

    public function removeParticipant(SchoolOuting $outing, SchoolOutingParticipant $participant, int $schoolId): void
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->authorizeParticipant($outing, $participant, $schoolId);
        $this->assertUnlocked($outing);
        abort_if($participant->contributions()->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'No se puede eliminar un deportista con abonos registrados.');
        $participant->delete();
    }

    public function createActivity(SchoolOuting $outing, array $data, int $schoolId): SchoolOutingActivity
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->assertUnlocked($outing);
        return $outing->activities()->create($data + ['school_id' => $schoolId, 'is_default' => false]);
    }

    public function updateActivity(SchoolOuting $outing, SchoolOutingActivity $activity, array $data, int $schoolId): SchoolOutingActivity
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->authorizeActivity($outing, $activity, $schoolId);
        $this->assertUnlocked($outing);
        $activity->update($data);
        return $activity->fresh();
    }

    public function deleteActivity(SchoolOuting $outing, SchoolOutingActivity $activity, int $schoolId): void
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->authorizeActivity($outing, $activity, $schoolId);
        $this->assertUnlocked($outing);
        abort_if($activity->contributions()->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'No se puede eliminar una actividad con abonos registrados.');
        $activity->delete();
    }

    public function createContribution(SchoolOuting $outing, array $data, int $schoolId, int $userId): SchoolOutingContribution
    {
        $this->authorizeOuting($outing, $schoolId);
        $this->assertUnlocked($outing);
        $participant = SchoolOutingParticipant::query()->findOrFail($data['school_outing_participant_id']);
        $activity = SchoolOutingActivity::query()->findOrFail($data['school_outing_activity_id']);
        $this->authorizeParticipant($outing, $participant, $schoolId);
        $this->authorizeActivity($outing, $activity, $schoolId);

        return SchoolOutingContribution::query()->create($data + ['school_outing_id' => $outing->id, 'school_id' => $schoolId, 'created_by' => $userId]);
    }

    public function authorizeOuting(SchoolOuting $outing, int $schoolId): void
    {
        abort_unless((int) $outing->school_id === $schoolId, Response::HTTP_NOT_FOUND);
    }

    private function authorizeParticipant(SchoolOuting $outing, SchoolOutingParticipant $participant, int $schoolId): void
    {
        abort_unless((int) $participant->school_outing_id === (int) $outing->id && (int) $participant->school_id === $schoolId, Response::HTTP_NOT_FOUND);
    }

    private function authorizeActivity(SchoolOuting $outing, SchoolOutingActivity $activity, int $schoolId): void
    {
        abort_unless((int) $activity->school_outing_id === (int) $outing->id && (int) $activity->school_id === $schoolId, Response::HTTP_NOT_FOUND);
    }

    private function assertUnlocked(SchoolOuting $outing): void
    {
        abort_if($outing->isLocked(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Las salidas cerradas o canceladas son de solo lectura.');
    }
}
