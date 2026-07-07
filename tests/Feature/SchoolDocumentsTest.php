<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolDocument;
use App\Models\SchoolUser;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SchoolDocumentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_school_can_upload_list_download_and_delete_club_document(): void
    {
        $response = $this->actingAs($this->user)->post('/api/v2/club-documents', [
            'title' => 'Certificado legal',
            'description' => 'Documento vigente',
            'file' => UploadedFile::fake()->create('certificado.pdf', 100, 'application/pdf'),
        ])->assertCreated()->assertJsonPath('data.title', 'Certificado legal');

        $document = SchoolDocument::findOrFail((int) $response->json('data.id'));
        $this->assertSame($this->school['id'], $document->school_id);
        $this->assertSame(SchoolDocument::SCOPE_CLUB, $document->scope);
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($this->user)->getJson('/api/v2/club-documents')
            ->assertOk()->assertJsonPath('data.0.original_name', 'certificado.pdf');

        $this->actingAs($this->user)->get("/api/v2/club-documents/{$document->id}/download")
            ->assertOk()->assertDownload('certificado.pdf');

        $this->actingAs($this->user)->deleteJson("/api/v2/club-documents/{$document->id}")
            ->assertOk();
        $this->assertDatabaseMissing('school_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->path);
    }

    public function test_instructor_can_manage_planning_documents_but_cannot_access_club_documents(): void
    {
        $instructor = $this->createUser([
            'email' => 'documents-instructor@example.com',
            'school_id' => $this->school['id'],
        ], [User::INSTRUCTOR]);
        SchoolUser::query()->create(['school_id' => $this->school['id'], 'user_id' => $instructor->id]);

        $this->actingAs($instructor)->post('/api/v2/document-planning', [
            'title' => 'Programa de psicología',
            'file' => UploadedFile::fake()->create('programa.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertCreated();

        $this->actingAs($instructor)->getJson('/api/v2/document-planning')->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($instructor)->getJson('/api/v2/club-documents')->assertForbidden();
        $this->actingAs($instructor)->post('/api/v2/club-documents', [])->assertForbidden();
    }

    public function test_documents_are_isolated_by_school_and_scope(): void
    {
        $otherSchool = School::factory()->create(['slug' => 'other-documents-school']);
        $own = $this->document((int) $this->school['id'], SchoolDocument::SCOPE_CLUB);
        $other = $this->document($otherSchool->id, SchoolDocument::SCOPE_CLUB);
        $planning = $this->document((int) $this->school['id'], SchoolDocument::SCOPE_PLANNING);

        $response = $this->actingAs($this->user)->getJson('/api/v2/club-documents')->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($own->id));
        $this->assertFalse($ids->contains($other->id));
        $this->assertFalse($ids->contains($planning->id));

        $this->actingAs($this->user)->getJson("/api/v2/club-documents/{$other->id}/download")->assertNotFound();
        $this->actingAs($this->user)->deleteJson("/api/v2/club-documents/{$planning->id}")->assertNotFound();
    }

    public function test_upload_validation_rejects_unsupported_or_oversized_files(): void
    {
        $this->actingAs($this->user)->post('/api/v2/club-documents', [
            'title' => 'Archivo inválido',
            'file' => UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');

        $this->actingAs($this->user)->post('/api/v2/club-documents', [
            'title' => 'Archivo grande',
            'file' => UploadedFile::fake()->create('grande.pdf', 20 * 1024 + 1, 'application/pdf'),
        ])->assertSessionHasErrors('file');
    }

    public function test_each_permission_can_disable_its_api_independently(): void
    {
        $school = School::findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.club_documents'] = false;
        $school->update(['school_permissions' => $permissions]);

        $this->actingAs($this->user)->getJson('/api/v2/club-documents')->assertForbidden();
        $this->actingAs($this->user)->getJson('/api/v2/document-planning')->assertOk();
    }

    private function document(int $schoolId, string $scope): SchoolDocument
    {
        $folder = $scope === SchoolDocument::SCOPE_CLUB ? 'club' : 'planning';
        $path = "school/documents/{$folder}/file.pdf";
        Storage::disk('local')->put($path, 'pdf');

        return SchoolDocument::query()->create([
            'school_id' => $schoolId,
            'uploaded_by' => $this->user->id,
            'scope' => $scope,
            'title' => 'Documento',
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'file.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 3,
        ]);
    }
}
