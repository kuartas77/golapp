<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MethodologyRecord;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/methodology_records?draw=1&start=0&length=10&type=planning')
            ->assertOk()
            ->assertJsonPath('data.0.id', $recordId)
            ->assertJsonPath('data.0.creator_name', e($this->user->name))
            ->assertJsonPath('data.0.session_date', '2026-07-15')
            ->assertJsonPath('data.0.export_pdf_url', route('methodology.records.pdf', ['id' => $recordId]));

        $this->actingAs($this->user)
            ->getJson("/api/v2/methodology-records/{$recordId}")
            ->assertOk()
            ->assertJsonPath('data.fields.objective', 'Mejorar pase')
            ->assertJsonPath('data.session_date', '2026-07-15')
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

    public function test_planning_record_can_store_phase_image_visual_resource(): void
    {
        Storage::fake('public');

        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $response = $this->actingAs($this->user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/methodology-records', array_replace_recursive($this->payload([
                'training_group_id' => $group->id,
            ]), [
                'diagram_media' => [
                    'initial_phase' => ['mode' => 'image', 'image_remove' => '0'],
                ],
                'diagram_images' => [
                    'initial_phase' => UploadedFile::fake()->image('fase-inicial.jpg')->size(512),
                ],
            ]))
            ->assertCreated()
            ->assertJsonPath('data.diagram_media.initial_phase.mode', 'image');

        $path = MethodologyRecord::findOrFail((int) $response->json('data.id'))->diagram_media['initial_phase']['path'];

        $this->assertStringStartsWith(School::findOrFail($this->school['id'])->slug . '/methodology/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame(route('images', $path), $response->json('data.diagram_media.initial_phase.image_url'));
    }

    public function test_methodology_phase_image_cannot_exceed_five_megabytes(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/methodology-records', array_replace_recursive($this->payload(), [
                'diagram_media' => [
                    'initial_phase' => ['mode' => 'image', 'image_remove' => '0'],
                ],
                'diagram_images' => [
                    'initial_phase' => UploadedFile::fake()->image('fase-grande.jpg')->size(5121),
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('diagram_images.initial_phase');
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

        $datatableResponse = $this->actingAs($instructorA)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/methodology_records?draw=1&start=0&length=10&type=planning')
            ->assertOk();

        $datatableIds = collect($datatableResponse->json('data'))->pluck('id')->all();

        $this->assertContains($ownRecord->id, $datatableIds);
        $this->assertNotContains($blockedRecord->id, $datatableIds);

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

        $this->actingAs($instructorA)
            ->deleteJson("/api/v2/methodology-records/{$blockedRecord->id}")
            ->assertNotFound();
    }

    public function test_accessible_methodology_records_can_be_deleted(): void
    {
        $record = $this->createRecord((int) $this->school['id'], $this->user, [
            'title' => 'Registro para eliminar',
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v2/methodology-records/{$record->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Registro metodológico eliminado correctamente.');

        $this->assertSoftDeleted('methodology_records', [
            'id' => $record->id,
        ]);
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

    public function test_methodology_filter_options_are_loaded_outside_datatable_rows(): void
    {
        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'methodology-filter-instructor@example.com');
        $otherSchool = School::factory()->create([
            'email' => 'methodology-filter-other@example.com',
            'slug' => 'methodology-filter-other',
        ]);
        $otherInstructor = $this->createSchoolScopedUser($otherSchool->id, ['instructor'], 'methodology-filter-other-instructor@example.com');

        TrainingGroup::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Sub 13',
            'stage' => 'Avanzado',
            'year' => now()->year,
            'year_active' => now()->year,
            'days' => 'Lunes',
            'schedules' => '08:00 AM - 09:00 AM',
        ]);
        TrainingGroup::query()->create([
            'school_id' => $otherSchool->id,
            'name' => 'Oculto',
            'year' => now()->year,
            'year_active' => now()->year,
        ]);

        $this->createRecord((int) $this->school['id'], $instructor);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/methodology-records/filters')
            ->assertOk();

        $creatorLabels = collect($response->json('data.creators'))->pluck('label')->all();
        $groupLabels = collect($response->json('data.training_groups'))->pluck('label')->all();

        $this->assertContains($instructor->name, $creatorLabels);
        $this->assertNotContains($otherInstructor->name, $creatorLabels);
        $this->assertContains('Sub 13 - Avanzado', $groupLabels);
        $this->assertNotContains('Oculto', $groupLabels);

        $instructorResponse = $this->actingAs($instructor)
            ->getJson('/api/v2/methodology-records/filters')
            ->assertOk();

        $this->assertSame(
            [$instructor->name],
            collect($instructorResponse->json('data.creators'))->pluck('label')->all(),
        );
    }

    public function test_methodology_datatable_filters_session_date_by_month_quarter_and_semester(): void
    {
        $julyRecord = $this->createRecord((int) $this->school['id'], $this->user, [
            'title' => 'Julio',
            'fields' => ['session_date' => '2025-07-15'],
        ]);
        $augustRecord = $this->createRecord((int) $this->school['id'], $this->user, [
            'title' => 'Agosto',
            'fields' => ['session_date' => '2026-08-20'],
        ]);
        $februaryRecord = $this->createRecord((int) $this->school['id'], $this->user, [
            'title' => 'Febrero',
            'fields' => ['session_date' => '2026-02-10'],
        ]);
        $otherSchool = School::factory()->create([
            'email' => 'methodology-date-other@example.com',
            'slug' => 'methodology-date-other',
        ]);
        $this->createRecord($otherSchool->id, $this->user, [
            'title' => 'Agosto oculto',
            'fields' => ['session_date' => '2026-08-21'],
        ]);

        $monthResponse = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson($this->methodologyDatatableUrl('month:7'))
            ->assertOk();

        $this->assertSame([$julyRecord->id], collect($monthResponse->json('data'))->pluck('id')->all());

        $quarterResponse = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson($this->methodologyDatatableUrl('quarter:3'))
            ->assertOk();

        $quarterIds = collect($quarterResponse->json('data'))->pluck('id')->all();
        $this->assertContains($julyRecord->id, $quarterIds);
        $this->assertContains($augustRecord->id, $quarterIds);
        $this->assertNotContains($februaryRecord->id, $quarterIds);

        $semesterResponse = $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson($this->methodologyDatatableUrl('semester:2'))
            ->assertOk();

        $semesterIds = collect($semesterResponse->json('data'))->pluck('id')->all();
        $this->assertContains($julyRecord->id, $semesterIds);
        $this->assertContains($augustRecord->id, $semesterIds);
        $this->assertNotContains($februaryRecord->id, $semesterIds);

        $instructor = $this->createSchoolScopedUser((int) $this->school['id'], ['instructor'], 'methodology-date-instructor@example.com');
        $ownInstructorRecord = $this->createRecord((int) $this->school['id'], $instructor, [
            'title' => 'Agosto instructor',
            'fields' => ['session_date' => '2026-08-22'],
        ]);

        $instructorResponse = $this->actingAs($instructor)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson($this->methodologyDatatableUrl('month:8'))
            ->assertOk();

        $this->assertSame([$ownInstructorRecord->id], collect($instructorResponse->json('data'))->pluck('id')->all());
    }

    public function test_monthly_reports_derive_report_month_from_report_date(): void
    {
        $monthlyResponse = $this->actingAs($this->user)
            ->postJson('/api/v2/methodology-records', $this->payload([
                'type' => MethodologyRecord::TYPE_MONTHLY_REPORT,
                'title' => 'Informe mensual mayo',
                'fields' => [
                    'session_date' => '2026-05-10',
                    'report_month' => 'Enero',
                ],
            ]))
            ->assertCreated();

        $this->assertSame('Mayo', $monthlyResponse->json('data.fields.report_month'));

        $categoryRecord = $this->createRecord((int) $this->school['id'], $this->user, [
            'type' => MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT,
            'title' => 'Informe categoría',
            'fields' => [
                'session_date' => '2026-01-15',
                'report_month' => 'Enero',
            ],
        ]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/methodology-records/{$categoryRecord->id}", $this->payload([
                'type' => MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT,
                'title' => 'Informe categoría actualizado',
                'fields' => [
                    'session_date' => '2026-08-20',
                    'report_month' => 'Enero',
                ],
            ]))
            ->assertOk()
            ->assertJsonPath('data.fields.report_month', 'Agosto');
    }

    public function test_methodology_records_require_date(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/v2/methodology-records', $this->payload([
                'type' => MethodologyRecord::TYPE_PLANNING,
                'title' => 'Planificación sin fecha',
                'fields' => [
                    'session_date' => '',
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fields.session_date');

        $this->actingAs($this->user)
            ->postJson('/api/v2/methodology-records', $this->payload([
                'type' => MethodologyRecord::TYPE_CATEGORY_MONTHLY_REPORT,
                'title' => 'Informe categoría sin fecha',
                'fields' => [
                    'session_date' => '',
                    'report_month' => 'Enero',
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fields.session_date');
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
                'session_date' => '2026-07-15',
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

    private function methodologyDatatableUrl(string $sessionDateFilter): string
    {
        return '/api/v2/datatables/methodology_records?' . http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'type' => MethodologyRecord::TYPE_PLANNING,
            'columns' => [
                ['data' => 'title', 'name' => 'title', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'creator_name', 'name' => 'creator_name', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'training_group_name', 'name' => 'training_group_name', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'session_date', 'name' => 'session_date', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => $sessionDateFilter, 'regex' => 'false']],
                ['data' => 'id', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [
                ['column' => 3, 'dir' => 'desc'],
            ],
            'search' => ['value' => '', 'regex' => 'false'],
        ]);
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
