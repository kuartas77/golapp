<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolOuting;
use App\Models\SchoolOutingActivity;
use App\Models\SchoolOutingParticipant;
use App\Models\TrainingGroup;
use Tests\TestCase;

final class SchoolOutingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_school_outings_permission_blocks_module_endpoints(): void
    {
        $school = School::findOrFail($this->school['id']);
        $this->setSchoolOutingsPermission($school, false);

        $this->actingAs($this->user)
            ->getJson('/api/v2/school-outings')
            ->assertForbidden();

        $this->setSchoolOutingsPermission($school, true);

        $this->actingAs($this->user)
            ->getJson('/api/v2/school-outings')
            ->assertOk();
    }

    public function test_outing_crud_creates_default_direct_payment_activity_and_is_school_scoped(): void
    {
        $otherSchool = School::factory()->create([
            'email' => 'school-outings-other@example.com',
            'slug' => 'school-outings-other',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v2/school-outings', [
                'name' => 'Campeonato en Medellin',
                'departure_date' => '2026-07-20',
                'amount_per_player' => 150000,
                'notes' => 'Viaje deportivo',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Campeonato en Medellin')
            ->assertJsonPath('data.activities.0.name', 'Pago directo')
            ->assertJsonPath('data.amount_per_player', '150000.00');

        $outingId = $response->json('data.id');

        SchoolOuting::query()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Salida externa',
            'departure_date' => '2026-08-01',
            'amount_per_player' => 50000,
            'status' => SchoolOuting::STATUS_OPEN,
        ]);

        $list = $this->actingAs($this->user)
            ->getJson('/api/v2/school-outings')
            ->assertOk()
            ->json('data');

        $this->assertTrue(collect($list)->contains('id', $outingId));
        $this->assertFalse(collect($list)->contains('name', 'Salida externa'));

        $this->actingAs($this->user)
            ->putJson("/api/v2/school-outings/{$outingId}", [
                'name' => 'Campeonato Nacional',
                'departure_date' => '2026-07-21',
                'amount_per_player' => 175000,
                'notes' => null,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Campeonato Nacional')
            ->assertJsonPath('data.amount_per_player', '175000.00');
    }

    public function test_eligible_inscriptions_filter_by_group_category_and_search(): void
    {
        $schoolId = (int) $this->school['id'];
        $outing = $this->createOuting($schoolId);
        $group = TrainingGroup::query()->where('school_id', $schoolId)->firstOrFail();
        $match = $this->createActiveInscription($schoolId, $group->id, 'SUB-10', 'Juan', 'Medellin', 'ABC-101');
        $otherCategory = $this->createActiveInscription($schoolId, $group->id, 'SUB-12', 'Carlos', 'Bogota', 'ABC-102');
        $otherSchool = School::factory()->create(['email' => 'outing-hidden@example.com']);
        $otherGroup = TrainingGroup::query()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Otro grupo',
            'category' => 'SUB-10',
            'days' => 'Lunes',
            'schedules' => '10:00AM - 11:00AM',
            'year_active' => now()->year,
        ]);
        $this->createActiveInscription($otherSchool->id, $otherGroup->id, 'SUB-10', 'Juan', 'Oculto', 'ABC-999');

        $response = $this->actingAs($this->user)
            ->getJson("/api/v2/school-outings/{$outing->id}/eligible-inscriptions?training_group_id={$group->id}&category=SUB-10&search=Juan")
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($otherCategory->id));
    }

    public function test_participants_copy_target_amount_and_contributions_update_totals(): void
    {
        $schoolId = (int) $this->school['id'];
        $outing = $this->createOuting($schoolId, 200000);
        $group = TrainingGroup::query()->where('school_id', $schoolId)->firstOrFail();
        $inscription = $this->createActiveInscription($schoolId, $group->id, 'SUB-11', 'Ana', 'Perez', 'SAL-001');

        $participantResponse = $this->actingAs($this->user)
            ->postJson("/api/v2/school-outings/{$outing->id}/participants", [
                'inscription_ids' => [$inscription->id],
            ])
            ->assertCreated()
            ->assertJsonPath('data.participants.0.target_amount', '200000.00')
            ->assertJsonPath('data.target_total', 200000);

        $participantId = $participantResponse->json('data.participants.0.id');
        $activityId = $participantResponse->json('data.activities.0.id');

        $this->actingAs($this->user)
            ->postJson("/api/v2/school-outings/{$outing->id}/contributions", [
                'school_outing_participant_id' => $participantId,
                'school_outing_activity_id' => $activityId,
                'amount' => 50000,
                'contribution_date' => '2026-07-01',
                'notes' => 'Primera rifa',
            ])
            ->assertCreated()
            ->assertJsonPath('outing.raised_total', 50000)
            ->assertJsonPath('outing.pending_total', 150000)
            ->assertJsonPath('outing.participants.0.raised_total', 50000)
            ->assertJsonPath('outing.participants.0.pending_total', 150000);
    }

    public function test_cannot_remove_participants_or_activities_with_contributions(): void
    {
        $schoolId = (int) $this->school['id'];
        $outing = $this->createOuting($schoolId);
        $group = TrainingGroup::query()->where('school_id', $schoolId)->firstOrFail();
        $inscription = $this->createActiveInscription($schoolId, $group->id, 'SUB-13', 'Luis', 'Rios', 'SAL-002');
        $activity = $outing->activities()->firstOrFail();

        $participant = SchoolOutingParticipant::query()->create([
            'school_outing_id' => $outing->id,
            'school_id' => $schoolId,
            'inscription_id' => $inscription->id,
            'player_id' => $inscription->player_id,
            'target_amount' => 100000,
        ]);

        $participant->contributions()->create([
            'school_outing_id' => $outing->id,
            'school_outing_activity_id' => $activity->id,
            'school_id' => $schoolId,
            'amount' => 10000,
            'contribution_date' => '2026-07-02',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/school-outings/{$outing->id}/participants/{$participant->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'No se puede eliminar un deportista con abonos registrados.');

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/school-outings/{$outing->id}/activities/{$activity->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'No se puede eliminar una actividad con abonos registrados.');
    }

    public function test_locked_outings_are_read_only_for_operational_changes(): void
    {
        $schoolId = (int) $this->school['id'];
        $outing = $this->createOuting($schoolId);
        $group = TrainingGroup::query()->where('school_id', $schoolId)->firstOrFail();
        $inscription = $this->createActiveInscription($schoolId, $group->id, 'SUB-14', 'Mario', 'Lopez', 'SAL-003');

        $this->actingAs($this->user)
            ->patchJson("/api/v2/school-outings/{$outing->id}/status", ['status' => SchoolOuting::STATUS_CLOSED])
            ->assertOk()
            ->assertJsonPath('data.status', SchoolOuting::STATUS_CLOSED);

        $this->actingAs($this->user)
            ->putJson("/api/v2/school-outings/{$outing->id}", [
                'name' => 'Nombre cerrado',
                'departure_date' => '2026-08-01',
                'amount_per_player' => 100000,
            ])
            ->assertUnprocessable();

        $this->actingAs($this->user)
            ->postJson("/api/v2/school-outings/{$outing->id}/participants", [
                'inscription_ids' => [$inscription->id],
            ])
            ->assertUnprocessable();
    }

    private function createOuting(int $schoolId, int $amount = 100000): SchoolOuting
    {
        $outing = SchoolOuting::query()->create([
            'school_id' => $schoolId,
            'name' => 'Salida de prueba',
            'departure_date' => '2026-07-20',
            'amount_per_player' => $amount,
            'status' => SchoolOuting::STATUS_OPEN,
            'created_by' => $this->user->id,
        ]);

        $outing->activities()->create([
            'school_id' => $schoolId,
            'name' => 'Pago directo',
            'is_default' => true,
        ]);

        return $outing;
    }

    private function createActiveInscription(
        int $schoolId,
        int $trainingGroupId,
        string $category,
        string $names,
        string $lastNames,
        string $uniqueCode
    ): Inscription {
        $player = Player::factory()->create([
            'school_id' => $schoolId,
            'names' => $names,
            'last_names' => $lastNames,
            'unique_code' => $uniqueCode,
            'category' => $category,
        ]);

        return Inscription::factory()->create([
            'school_id' => $schoolId,
            'player_id' => $player->id,
            'unique_code' => $uniqueCode,
            'year' => now()->year,
            'training_group_id' => $trainingGroupId,
            'competition_group_id' => null,
            'category' => $category,
        ]);
    }

    private function setSchoolOutingsPermission(School $school, bool $enabled): void
    {
        $school->forceFill([
            'school_permissions' => School::normalizeSchoolPermissions(array_merge(
                $school->getResolvedSchoolPermissions(),
                ['school.module.school_outings' => $enabled],
            )),
        ])->save();

        School::forgetCachedSchool($school->id);
    }
}
