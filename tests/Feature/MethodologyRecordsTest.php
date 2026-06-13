<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MethodologyRecord;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\User;
use Tests\TestCase;

final class MethodologyRecordsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_school_user_can_create_list_show_and_update_methodology_records(): void
    {
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        $response = $this->actingAs($this->user)
            ->postJson('/api/v2/methodology-records', $this->payload([
                'training_group_id' => $group->id,
            ]))
            ->assertCreated()
            ->assertJsonPath('data.type', MethodologyRecord::TYPE_PLANNING)
            ->assertJsonPath('data.creator_name', $this->user->name);

        $recordId = (int) $response->json('data.id');

        $this->assertDatabaseHas('methodology_records', [
            'id' => $recordId,
            'school_id' => $this->school['id'],
            'user_id' => $this->user->id,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'Planificación Sub 12',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/methodology-records?type=planning')
            ->assertOk()
            ->assertJsonPath('data.0.id', $recordId);

        $this->actingAs($this->user)
            ->getJson("/api/v2/methodology-records/{$recordId}")
            ->assertOk()
            ->assertJsonPath('data.fields.objective', 'Mejorar pase')
            ->assertJsonPath('data.diagrams.initial_phase.0.type', 'player')
            ->assertJsonPath('data.export_pdf_url', route('methodology.records.pdf', ['id' => $recordId]));

        $this->actingAs($this->user)
            ->get(route('methodology.records.pdf', ['id' => $recordId]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->user)
            ->putJson("/api/v2/methodology-records/{$recordId}", $this->payload([
                'title' => 'Planificación actualizada',
                'fields' => ['objective' => 'Mejorar presión'],
            ]))
            ->assertOk()
            ->assertJsonPath('data.title', 'Planificación actualizada')
            ->assertJsonPath('data.fields.objective', 'Mejorar presión');
    }

    public function test_records_are_scoped_by_selected_school(): void
    {
        $otherSchool = School::factory()->create([
            'email' => 'methodology-other-school@example.com',
            'slug' => 'methodology-other-school',
        ]);

        $ownRecord = $this->createRecord((int) $this->school['id'], $this->user);
        $otherRecord = $this->createRecord($otherSchool->id, $this->user, [
            'title' => 'Registro oculto',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/methodology-records')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ownRecord->id, $ids);
        $this->assertNotContains($otherRecord->id, $ids);

        $this->actingAs($this->user)
            ->getJson("/api/v2/methodology-records/{$otherRecord->id}")
            ->assertNotFound();
    }

    public function test_instructors_only_access_records_they_created(): void
    {
        $instructorA = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'methodology-a@example.com');
        $instructorB = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'methodology-b@example.com');

        $ownRecord = $this->createRecord((int) $this->school['id'], $instructorA, [
            'title' => 'Registro propio',
        ]);
        $blockedRecord = $this->createRecord((int) $this->school['id'], $instructorB, [
            'title' => 'Registro de otro instructor',
        ]);

        $response = $this->actingAs($instructorA)
            ->getJson('/api/v2/methodology-records')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($ownRecord->id, $ids);
        $this->assertNotContains($blockedRecord->id, $ids);

        $this->actingAs($instructorA)
            ->getJson("/api/v2/methodology-records/{$ownRecord->id}")
            ->assertOk();

        $this->actingAs($instructorA)
            ->getJson("/api/v2/methodology-records/{$blockedRecord->id}")
            ->assertNotFound();

        $this->actingAs($instructorA)
            ->putJson("/api/v2/methodology-records/{$blockedRecord->id}", $this->payload([
                'title' => 'Intento bloqueado',
            ]))
            ->assertNotFound();
    }

    public function test_school_and_super_admin_can_access_all_school_records(): void
    {
        $superAdmin = $this->createSchoolScopedUser((int) $this->school['id'], ['super-admin'], 'methodology-super@example.com');
        $instructorA = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'methodology-visible-a@example.com');
        $instructorB = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'methodology-visible-b@example.com');

        $recordA = $this->createRecord((int) $this->school['id'], $instructorA, ['title' => 'A']);
        $recordB = $this->createRecord((int) $this->school['id'], $instructorB, ['title' => 'B']);

        $schoolResponse = $this->actingAs($this->user)
            ->getJson('/api/v2/methodology-records')
            ->assertOk();

        $schoolIds = collect($schoolResponse->json('data'))->pluck('id')->all();

        $this->assertContains($recordA->id, $schoolIds);
        $this->assertContains($recordB->id, $schoolIds);

        $adminResponse = $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/methodology-records')
            ->assertOk();

        $adminIds = collect($adminResponse->json('data'))->pluck('id')->all();

        $this->assertContains($recordA->id, $adminIds);
        $this->assertContains($recordB->id, $adminIds);
    }

    public function test_non_planning_records_drop_diagrams(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v2/methodology-records', $this->payload([
                'type' => MethodologyRecord::TYPE_MONTHLY_REPORT,
                'title' => 'Informe mensual',
                'diagrams' => ['initial_phase' => [['type' => 'player']]],
            ]))
            ->assertCreated();

        $this->assertSame([], $response->json('data.diagrams'));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'training_group_id' => null,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'Planificación Sub 12',
            'fields' => [
                'objective' => 'Mejorar pase',
                'material' => 'Conos y balones',
            ],
            'diagrams' => [
                'initial_phase' => [
                    ['id' => 'one', 'type' => 'player', 'x' => 50, 'y' => 32, 'label' => ''],
                ],
            ],
        ], $overrides);
    }

    private function createRecord(int $schoolId, User $creator, array $overrides = []): MethodologyRecord
    {
        return MethodologyRecord::query()->create(array_replace([
            'school_id' => $schoolId,
            'user_id' => $creator->id,
            'training_group_id' => null,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'title' => 'Registro metodológico',
            'fields' => ['objective' => 'Trabajo técnico'],
            'diagrams' => ['initial_phase' => []],
        ], $overrides));
    }

    private function createSchoolScopedUser(int $schoolId, array $roles, string $email): User
    {
        $user = $this->createUser([
            'email' => $email,
            'school_id' => $schoolId,
        ], $roles);

        SchoolUser::query()->create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
