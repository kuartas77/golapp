<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Imports\ImportPlayers;
use App\Jobs\ProcessPlayerImport;
use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\PlayerImport;
use App\Notifications\InscriptionNotification;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use App\Service\Import\ImportService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

final class ImportPlayersTest extends TestCase
{
    public function test_imported_players_get_an_inscription(): void
    {
        Notification::fake();

        $birthDate = now()->subYears(12)->startOfDay();
        $import = new ImportPlayers(
            (int) $this->school['id'],
            app(PlayerRepository::class),
            app(InscriptionRepository::class)
        );

        $import->collection(new Collection([
            new Collection([
                'fecha_de_nacimiento' => ExcelDate::PHPToExcel($birthDate),
                'numero_de_documento' => 'DOC-IMPORT-1',
                'nombres' => 'Carlos',
                'apellidos' => 'Perez',
                'genero' => 'M',
                'lugar_de_nacimiento' => 'Medellin',
                'rh' => 'O+',
                'escuela_o_colegio_donde_estudia' => 'Colegio Demo',
                'direccion_de_residencia' => 'Calle 1',
                'municipio' => 'Medellin',
                'barrio' => 'Centro',
                'correo_electronico' => 'importado@example.com',
                'numero_de_celular' => '3001234567',
                'eps' => 'Sura',
                'nombres_y_apellidos' => 'Acudiente Importado',
                'numero_de_telefono' => '6041234567',
                'profesion' => 'Ingeniera',
                'empresa' => 'Empresa Demo',
                'cargo' => 'Directora',
            ]),
        ]));

        $player = Player::query()
            ->where('school_id', $this->school['id'])
            ->where('identification_document', 'DOC-IMPORT-1')
            ->firstOrFail();

        $this->assertDatabaseHas('peoples', [
            'names' => 'ACUDIENTE IMPORTADO',
            'phone' => '6041234567',
        ]);

        $this->assertDatabaseHas('inscriptions', [
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'school_id' => $this->school['id'],
            'year' => getYearInscription(),
        ]);

        Notification::assertNotSentTo($player, InscriptionNotification::class);
    }

    public function test_api_import_players_endpoint_queues_and_tracks_the_import(): void
    {
        Notification::fake();
        Queue::fake();
        Storage::fake('local');

        $response = $this->actingAs($this->user)
            ->post('/api/v2/import/players', [
                'file' => $this->makeImportFile([
                    'fecha_de_nacimiento' => ExcelDate::PHPToExcel(now()->subYears(11)->startOfDay()),
                    'numero_de_documento' => 'DOC-IMPORT-API',
                    'nombres' => 'Andres',
                    'apellidos' => 'Restrepo',
                    'genero' => 'Masculino',
                    'lugar_de_nacimiento' => 'Cali',
                    'rh' => 'B+',
                    'escuela_o_colegio_donde_estudia' => 'Colegio API',
                    'direccion_de_residencia' => 'Avenida 1',
                    'municipio' => 'Cali',
                    'barrio' => 'Sur',
                    'correo_electronico' => 'api.import@example.com',
                    'numero_de_celular' => '3111234567',
                    'eps' => 'Sanitas',
                    'nombres_y_apellidos' => 'Acudiente API',
                    'numero_de_telefono' => '6021234567',
                    'profesion' => 'Administrador',
                    'empresa' => 'API Demo',
                    'cargo' => 'Coordinador',
                ]),
            ], ['Accept' => 'application/json'])
            ->assertAccepted()
            ->assertJsonPath('success', true)
            ->assertJsonPath('import.status', PlayerImport::STATUS_PENDING);

        $playerImport = PlayerImport::query()->firstOrFail();
        Storage::disk('local')->assertExists($playerImport->path);
        Queue::assertPushed(ProcessPlayerImport::class, fn (ProcessPlayerImport $job) => $job->playerImportId === $playerImport->id);

        $job = Queue::pushed(ProcessPlayerImport::class)->first();
        $job->handle(app(ImportService::class));

        $playerImport->refresh();
        $this->assertSame(PlayerImport::STATUS_COMPLETED, $playerImport->status);
        $this->assertSame(1, $playerImport->summary['created_players']);
        $this->assertSame(1, $playerImport->summary['created_inscriptions']);
        Storage::disk('local')->assertMissing($playerImport->path);

        $player = Player::query()
            ->where('school_id', $this->school['id'])
            ->where('identification_document', 'DOC-IMPORT-API')
            ->firstOrFail();

        $this->assertDatabaseHas('inscriptions', [
            'player_id' => $player->id,
            'school_id' => $this->school['id'],
            'year' => getYearInscription(),
        ]);
        Notification::assertNotSentTo($player, InscriptionNotification::class);

        $this->actingAs($this->user)
            ->getJson("/api/v2/import/players/{$response->json('import.id')}")
            ->assertOk()
            ->assertJsonPath('import.status', PlayerImport::STATUS_COMPLETED)
            ->assertJsonPath('import.summary.created_players', 1);
    }

    public function test_queued_import_exposes_validation_failure_and_removes_the_file(): void
    {
        Queue::fake();
        Storage::fake('local');

        $response = $this->actingAs($this->user)
            ->post('/api/v2/import/players', [
                'file' => $this->makeImportFile($this->playerRow([
                    'numero_de_documento' => 'DOC-IMPORT-JOB-INVALIDO',
                    'nombres_y_apellidos' => 'Acudiente Incompleto',
                    'numero_de_telefono' => '',
                ])),
            ], ['Accept' => 'application/json'])
            ->assertAccepted();

        $playerImport = PlayerImport::query()->firstOrFail();
        $job = Queue::pushed(ProcessPlayerImport::class)->first();

        try {
            $job->handle(app(ImportService::class));
            $this->fail('The queued import should have failed validation.');
        } catch (ValidationException) {
            // The job persists a safe validation message before letting the queue mark it as failed.
        }

        $playerImport->refresh();
        $this->assertSame(PlayerImport::STATUS_FAILED, $playerImport->status);
        $this->assertSame(
            'Fila 2: completa numero_de_telefono del acudiente o deja los datos del acudiente vacíos.',
            $playerImport->error_message
        );
        Storage::disk('local')->assertMissing($playerImport->path);

        $this->actingAs($this->user)
            ->getJson("/api/v2/import/players/{$response->json('import.id')}")
            ->assertOk()
            ->assertJsonPath('import.status', PlayerImport::STATUS_FAILED)
            ->assertJsonPath('import.error_message', $playerImport->error_message);
    }

    public function test_a_school_cannot_queue_two_player_imports_at_the_same_time(): void
    {
        Queue::fake();
        Storage::fake('local');

        $firstResponse = $this->actingAs($this->user)
            ->post('/api/v2/import/players', [
                'file' => $this->makeImportFile($this->playerRow([
                    'numero_de_documento' => 'DOC-IMPORT-EN-COLA-1',
                ])),
            ], ['Accept' => 'application/json'])
            ->assertAccepted();

        $this->actingAs($this->user)
            ->post('/api/v2/import/players', [
                'file' => $this->makeImportFile($this->playerRow([
                    'numero_de_documento' => 'DOC-IMPORT-EN-COLA-2',
                ])),
            ], ['Accept' => 'application/json'])
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('import.id', $firstResponse->json('import.id'));

        $this->assertDatabaseCount('player_imports', 1);
        Queue::assertPushed(ProcessPlayerImport::class, 1);
    }

    public function test_imported_player_can_be_created_without_a_guardian(): void
    {
        Notification::fake();

        $summary = $this->importRows([
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-SIN-ACUDIENTE',
                'nombres_y_apellidos' => '',
                'numero_de_telefono' => '',
            ]),
        ]);

        $this->assertSame(1, $summary['created_players']);
        $this->assertSame(1, $summary['created_inscriptions']);

        $player = Player::query()
            ->where('school_id', $this->school['id'])
            ->where('identification_document', 'DOC-IMPORT-SIN-ACUDIENTE')
            ->firstOrFail();

        $this->assertFalse($player->people()->exists());
        $this->assertDatabaseHas('inscriptions', [
            'player_id' => $player->id,
            'school_id' => $this->school['id'],
            'year' => getYearInscription(),
        ]);
        $this->assertSame(0, People::query()->count());
    }

    public function test_import_rejects_partial_guardian_data_without_creating_the_player(): void
    {
        $this->assertImportValidationError(
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-ACUDIENTE-PARCIAL',
                'nombres_y_apellidos' => 'Acudiente Incompleto',
                'numero_de_telefono' => '',
            ]),
            'Fila 2: completa numero_de_telefono del acudiente o deja los datos del acudiente vacíos.'
        );

        $this->assertDatabaseMissing('players', [
            'school_id' => $this->school['id'],
            'identification_document' => 'DOC-IMPORT-ACUDIENTE-PARCIAL',
        ]);
    }

    public function test_import_rejects_guardian_phone_without_guardian_name(): void
    {
        $this->assertImportValidationError(
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-ACUDIENTE-SIN-NOMBRE',
                'nombres_y_apellidos' => '',
                'numero_de_telefono' => '6049876543',
            ]),
            'Fila 2: completa nombres_y_apellidos del acudiente o deja los datos del acudiente vacíos.'
        );

        $this->assertDatabaseMissing('players', [
            'school_id' => $this->school['id'],
            'identification_document' => 'DOC-IMPORT-ACUDIENTE-SIN-NOMBRE',
        ]);
    }

    public function test_import_accepts_identification_card_as_the_guardian_document(): void
    {
        $this->importRows([
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-DOCUMENTO-ACUDIENTE',
                'nombres_y_apellidos' => 'Acudiente Con Documento',
                'numero_de_telefono' => '6041112233',
                'identification_card' => 'CC-987654321',
            ]),
        ]);

        $this->assertDatabaseHas('peoples', [
            'names' => 'ACUDIENTE CON DOCUMENTO',
            'phone' => '6041112233',
            'identification_card' => 'CC-987654321',
        ]);
    }

    public function test_import_rejects_guardian_document_without_name_and_phone(): void
    {
        $this->assertImportValidationError(
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-SOLO-DOCUMENTO-ACUDIENTE',
                'nombres_y_apellidos' => '',
                'numero_de_telefono' => '',
                'identification_card' => 'CC-123456789',
            ]),
            'Fila 2: completa nombres_y_apellidos y numero_de_telefono del acudiente o deja los datos del acudiente vacíos.'
        );

        $this->assertDatabaseMissing('players', [
            'school_id' => $this->school['id'],
            'identification_document' => 'DOC-IMPORT-SOLO-DOCUMENTO-ACUDIENTE',
        ]);
    }

    public function test_reimport_without_guardian_data_preserves_existing_guardian(): void
    {
        $guardian = People::factory()->create([
            'names' => 'ACUDIENTE EXISTENTE',
            'identification_card' => 'ACUDIENTE-EXISTENTE-1',
            'tutor' => true,
        ]);
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'identification_document' => 'DOC-IMPORT-CONSERVA-ACUDIENTE',
        ]);
        $player->people()->attach($guardian);

        $summary = $this->importRows([
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-CONSERVA-ACUDIENTE',
                'nombres_y_apellidos' => '',
                'numero_de_telefono' => '',
            ]),
        ]);

        $this->assertSame(1, $summary['updated_players']);
        $this->assertTrue($player->people()->whereKey($guardian->id)->exists());
    }

    public function test_reimporting_player_updates_data_without_creating_duplicate_inscription(): void
    {
        Notification::fake();

        $birthDate = now()->subYears(10)->startOfDay();
        $row = new Collection([
            'fecha_de_nacimiento' => ExcelDate::PHPToExcel($birthDate),
            'numero_de_documento' => 'DOC-IMPORT-2',
            'nombres' => 'Laura',
            'apellidos' => 'Gomez',
            'genero' => 'F',
            'lugar_de_nacimiento' => 'Bogota',
            'rh' => 'A+',
            'escuela_o_colegio_donde_estudia' => 'Colegio Inicial',
            'direccion_de_residencia' => 'Carrera 1',
            'municipio' => 'Bogota',
            'barrio' => 'Norte',
            'correo_electronico' => 'laura@example.com',
            'numero_de_celular' => '3000000000',
            'eps' => 'Nueva EPS',
            'nombres_y_apellidos' => 'Acudiente Uno',
            'numero_de_telefono' => '6010000000',
            'profesion' => '',
            'empresa' => '',
            'cargo' => '',
        ]);

        $import = new ImportPlayers(
            (int) $this->school['id'],
            app(PlayerRepository::class),
            app(InscriptionRepository::class)
        );

        $import->collection(new Collection([$row]));
        $player = Player::query()->where('identification_document', 'DOC-IMPORT-2')->firstOrFail();
        $originalUniqueCode = $player->unique_code;

        $row->put('nombres', 'Laura Maria');
        $row->put('correo_electronico', 'laura.maria@example.com');
        $import->collection(new Collection([$row]));

        $player->refresh();

        $this->assertSame('LAURA MARIA', $player->names);
        $this->assertSame('laura.maria@example.com', $player->email);
        $this->assertSame($originalUniqueCode, $player->unique_code);
        $this->assertSame(1, Player::query()->where('identification_document', 'DOC-IMPORT-2')->count());
        $this->assertSame(1, Inscription::query()->where('player_id', $player->id)->where('year', getYearInscription())->count());
    }

    public function test_reimporting_an_existing_player_does_not_consume_a_unique_code(): void
    {
        $year = (string) now()->year;
        $schoolId = (int) $this->school['id'];

        Player::factory()->create([
            'school_id' => $schoolId,
            'identification_document' => 'DOC-IMPORT-CODIGO-EXISTENTE',
            'unique_code' => $year.'0283',
        ]);
        Cache::forget('KEY_LAST_UNIQUE_CODE_'.$schoolId);

        $this->importRows([
            $this->playerRow([
                'numero_de_documento' => 'DOC-IMPORT-CODIGO-EXISTENTE',
            ]),
        ]);

        $this->assertSame($year.'0284', (string) createUniqueCode((string) $schoolId));
    }

    public function test_bulk_import_reports_summary(): void
    {
        Notification::fake();

        $existing = Player::factory()->create([
            'school_id' => $this->school['id'],
            'identification_document' => 'DOC-IMPORT-BULK-1',
            'unique_code' => createUniqueCode((string) $this->school['id']),
        ]);

        Inscription::query()->create([
            'player_id' => $existing->id,
            'school_id' => $this->school['id'],
            'unique_code' => $existing->unique_code,
            'year' => getYearInscription(),
            'training_group_id' => 1,
            'competition_group_id' => null,
            'start_date' => now()->format('Y-m-d'),
            'category' => $existing->category,
            'photos' => false,
            'scholarship' => false,
            'copy_identification_document' => false,
            'eps_certificate' => false,
            'medic_certificate' => false,
            'study_certificate' => false,
            'overalls' => false,
            'ball' => false,
            'bag' => false,
            'presentation_uniform' => false,
            'competition_uniform' => false,
            'tournament_pay' => false,
        ]);

        $summary = $this->importRows([
            [
                'fecha_de_nacimiento' => ExcelDate::PHPToExcel(now()->subYears(11)->startOfDay()),
                'numero_de_documento' => 'DOC-IMPORT-BULK-1',
                'nombres' => 'Actualizado',
                'apellidos' => 'Uno',
                'genero' => 'M',
                'lugar_de_nacimiento' => 'Medellin',
                'rh' => 'O+',
                'escuela_o_colegio_donde_estudia' => 'Colegio Bulk',
                'direccion_de_residencia' => 'Calle 1',
                'municipio' => 'Medellin',
                'barrio' => 'Centro',
                'correo_electronico' => 'bulk1@example.com',
                'numero_de_celular' => '3000000001',
                'eps' => 'Sura',
                'nombres_y_apellidos' => 'Acudiente Bulk',
                'numero_de_telefono' => '6040000001',
                'profesion' => '',
                'empresa' => '',
                'cargo' => '',
            ],
            [
                'fecha_de_nacimiento' => ExcelDate::PHPToExcel(now()->subYears(12)->startOfDay()),
                'numero_de_documento' => 'DOC-IMPORT-BULK-2',
                'nombres' => 'Nuevo',
                'apellidos' => 'Dos',
                'genero' => 'F',
                'lugar_de_nacimiento' => 'Cali',
                'rh' => 'A+',
                'escuela_o_colegio_donde_estudia' => 'Colegio Bulk',
                'direccion_de_residencia' => 'Calle 2',
                'municipio' => 'Cali',
                'barrio' => 'Sur',
                'correo_electronico' => 'bulk2@example.com',
                'numero_de_celular' => '3000000002',
                'eps' => 'Sanitas',
                'nombres_y_apellidos' => 'Acudiente Bulk',
                'numero_de_telefono' => '6040000001',
                'profesion' => '',
                'empresa' => '',
                'cargo' => '',
            ],
        ]);

        $this->assertSame([
            'created_players' => 1,
            'updated_players' => 1,
            'created_inscriptions' => 1,
            'skipped_inscriptions' => 1,
        ], $summary);

        $this->assertSame('ACTUALIZADO', $existing->refresh()->names);
        $this->assertSame(1, Inscription::query()->where('player_id', $existing->id)->where('year', getYearInscription())->count());
    }

    public function test_bulk_import_reuses_school_and_setting_queries_across_rows(): void
    {
        Notification::fake();

        $schoolQueries = 0;
        $settingQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$schoolQueries, &$settingQueries): void {
            $sql = str_replace(['`', '"'], '', strtolower($query->sql));

            if (str_contains($sql, 'from schools')) {
                $schoolQueries++;
            }

            if (str_contains($sql, 'from setting_values')) {
                $settingQueries++;
            }
        });

        $this->importRows([
            $this->playerRow(['numero_de_documento' => 'DOC-IMPORT-N1-1']),
            $this->playerRow(['numero_de_documento' => 'DOC-IMPORT-N1-2']),
            $this->playerRow(['numero_de_documento' => 'DOC-IMPORT-N1-3']),
        ]);

        $this->assertLessThanOrEqual(2, $schoolQueries);
        $this->assertLessThanOrEqual(2, $settingQueries);
    }

    private function makeImportFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $rows = array_is_list($rows) ? $rows : [$rows];
        $headers = array_keys($rows[0]);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(array_map('array_values', $rows), null, 'A2');

        $path = storage_path('framework/testing/import_players_'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            path: $path,
            originalName: 'import_players.xlsx',
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            test: true
        );
    }

    private function importRows(array $rows): array
    {
        $import = new ImportPlayers(
            (int) $this->school['id'],
            app(PlayerRepository::class),
            app(InscriptionRepository::class)
        );

        $import->collection(collect($rows)->map(fn (array $row) => new Collection($row)));

        return $import->summary();
    }

    private function assertImportValidationError(array $row, string $expectedMessage): void
    {
        try {
            $this->importRows([$row]);
            $this->fail('The import should have failed validation.');
        } catch (ValidationException $exception) {
            $this->assertSame($expectedMessage, $exception->validator->errors()->first('file'));
        }
    }

    private function playerRow(array $overrides = []): array
    {
        return array_replace([
            'fecha_de_nacimiento' => ExcelDate::PHPToExcel(now()->subYears(12)->startOfDay()),
            'numero_de_documento' => 'DOC-IMPORT-BASE',
            'nombres' => 'Deportista',
            'apellidos' => 'Importado',
            'genero' => 'M',
            'lugar_de_nacimiento' => 'Medellin',
            'rh' => 'O+',
            'escuela_o_colegio_donde_estudia' => 'Colegio Demo',
            'direccion_de_residencia' => 'Calle 1',
            'municipio' => 'Medellin',
            'barrio' => 'Centro',
            'correo_electronico' => 'deportista.importado@example.com',
            'numero_de_celular' => '3001234567',
            'eps' => 'Sura',
            'nombres_y_apellidos' => 'Acudiente Importado',
            'numero_de_telefono' => '6041234567',
            'profesion' => '',
            'empresa' => '',
            'cargo' => '',
        ], $overrides);
    }
}
