<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateSchoolDataExport;
use App\Models\School;
use App\Models\SchoolDataExport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class SchoolDataExportsTest extends TestCase
{
    public function test_super_admin_can_request_and_list_school_data_exports(): void
    {
        Queue::fake();

        $superAdmin = $this->createUser([
            'email' => 'school-export-admin@example.com',
            'school_id' => $this->school['id'],
        ], ['super-admin']);

        $response = $this->actingAs($superAdmin)
            ->postJson("/api/v2/admin/schools/{$this->school['slug']}/data-exports");

        $response->assertCreated()
            ->assertJsonPath('data.status', SchoolDataExport::STATUS_PENDING);

        $this->assertDatabaseHas('school_data_exports', [
            'school_id' => $this->school['id'],
            'requested_by' => $superAdmin->id,
            'status' => SchoolDataExport::STATUS_PENDING,
        ]);

        Queue::assertPushed(GenerateSchoolDataExport::class);

        $listResponse = $this->actingAs($superAdmin)
            ->getJson("/api/v2/admin/schools/{$this->school['slug']}/data-exports");

        $listResponse->assertOk()
            ->assertJsonPath('data.0.school_id', $this->school['id']);
    }

    public function test_non_super_admin_cannot_request_school_data_exports(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user)
            ->postJson("/api/v2/admin/schools/{$this->school['slug']}/data-exports");

        $response->assertForbidden();
        Queue::assertNotPushed(GenerateSchoolDataExport::class);
    }

    public function test_school_cannot_start_two_active_data_exports_at_the_same_time(): void
    {
        Queue::fake();

        $superAdmin = $this->createUser([
            'email' => 'school-export-single-flight@example.com',
            'school_id' => $this->school['id'],
        ], ['super-admin']);

        SchoolDataExport::query()->create([
            'school_id' => $this->school['id'],
            'requested_by' => $superAdmin->id,
            'status' => SchoolDataExport::STATUS_PROCESSING,
            'disk' => 'export',
        ]);

        $response = $this->actingAs($superAdmin)
            ->postJson("/api/v2/admin/schools/{$this->school['slug']}/data-exports");

        $response->assertConflict()
            ->assertJsonPath('data.status', SchoolDataExport::STATUS_PROCESSING);

        Queue::assertNotPushed(GenerateSchoolDataExport::class);
    }

    public function test_ready_export_can_be_downloaded_by_super_admin(): void
    {
        Storage::fake('export');

        $superAdmin = $this->createUser([
            'email' => 'school-export-download@example.com',
            'school_id' => $this->school['id'],
        ], ['super-admin']);

        Storage::disk('export')->put('school-data-exports/test.zip', 'zip-content');

        $export = SchoolDataExport::query()->create([
            'school_id' => $this->school['id'],
            'requested_by' => $superAdmin->id,
            'status' => SchoolDataExport::STATUS_READY,
            'disk' => 'export',
            'path' => 'school-data-exports/test.zip',
            'filename' => 'test.zip',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($superAdmin)
            ->get("/api/v2/admin/schools/{$this->school['slug']}/data-exports/{$export->id}/download");

        $response->assertOk();
    }

    public function test_generator_creates_zip_manifest_and_school_scoped_files(): void
    {
        Storage::fake('public');
        Storage::fake('export');

        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill(['logo' => "{$school->slug}/logo.png"])->save();

        Storage::disk('public')->put("{$school->slug}/logo.png", 'logo');
        Storage::disk('public')->put("{$school->slug}/players/player-one.png", 'photo');

        $playerId = DB::table('players')->insertGetId([
            'unique_code' => 'PX001',
            'names' => 'Export',
            'last_names' => 'Player',
            'gender' => 'M',
            'date_birth' => '2012-01-01',
            'place_birth' => 'Bogota',
            'identification_document' => 'PX001',
            'photo' => "{$school->slug}/players/player-one.png",
            'school' => 'Colegio Export',
            'degree' => 'Quinto',
            'address' => 'Calle 1',
            'municipality' => 'Bogota',
            'neighborhood' => 'Centro',
            'phones' => '3000000000',
            'email' => 'player-one@example.com',
            'mobile' => '3000000001',
            'school_id' => $school->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('players')->insert([
            'unique_code' => 'PX002',
            'names' => 'Hidden',
            'last_names' => 'Player',
            'gender' => 'M',
            'date_birth' => '2012-01-01',
            'place_birth' => 'Bogota',
            'identification_document' => 'PX002',
            'school' => 'Colegio Hidden',
            'degree' => 'Quinto',
            'address' => 'Calle 2',
            'municipality' => 'Bogota',
            'neighborhood' => 'Centro',
            'phones' => '3000000002',
            'email' => 'player-two@example.com',
            'mobile' => '3000000003',
            'school_id' => School::factory()->create(['email' => 'hidden-export@example.com'])->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inscriptions')->insert([
            'player_id' => $playerId,
            'unique_code' => 'PX001',
            'year' => now()->year,
            'start_date' => now()->toDateString(),
            'training_group_id' => DB::table('training_groups')->where('school_id', $school->id)->value('id'),
            'category' => 'Sub 12',
            'school_id' => $school->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $superAdmin = $this->createUser([
            'email' => 'school-export-generator@example.com',
            'school_id' => $school->id,
        ], ['super-admin']);

        $export = SchoolDataExport::query()->create([
            'school_id' => $school->id,
            'requested_by' => $superAdmin->id,
            'status' => SchoolDataExport::STATUS_PENDING,
            'disk' => 'export',
        ]);

        (new GenerateSchoolDataExport($export->id))->handle(app(\App\Service\SchoolDataExport\SchoolDataExportGenerator::class));

        $export->refresh();

        $this->assertSame(SchoolDataExport::STATUS_READY, $export->status);
        Storage::disk('export')->assertExists($export->path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open(Storage::disk('export')->path($export->path)));

        $this->assertNotFalse($zip->locateName('README.txt'));
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertNotFalse($zip->locateName('datos/03_deportistas_acudientes/players.csv'));
        $this->assertNotFalse($zip->locateName('datos/04_inscripciones/inscriptions.csv'));

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $zip->close();

        $this->assertSame(1, $manifest['tables']['players']);
        $this->assertSame(1, $manifest['tables']['inscriptions']);
        $this->assertNotEmpty($manifest['files']);
        $this->assertCount(0, $manifest['warnings']);
    }
}
