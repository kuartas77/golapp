<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Imports\ImportPlayers;
use App\Models\Inscription;
use App\Models\Player;
use App\Notifications\InscriptionNotification;
use App\Repositories\InscriptionRepository;
use App\Repositories\PlayerRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
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

        Notification::assertSentTo($player, InscriptionNotification::class);
    }

    public function test_api_import_players_endpoint_creates_inscriptions(): void
    {
        Notification::fake();

        $this->actingAs($this->user)
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
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $player = Player::query()
            ->where('school_id', $this->school['id'])
            ->where('identification_document', 'DOC-IMPORT-API')
            ->firstOrFail();

        $this->assertDatabaseHas('inscriptions', [
            'player_id' => $player->id,
            'school_id' => $this->school['id'],
            'year' => getYearInscription(),
        ]);
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

    private function makeImportFile(array $row): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = array_keys($row);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(array_values($row), null, 'A2');

        $path = storage_path('framework/testing/import_players_'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            path: $path,
            originalName: 'import_players.xlsx',
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            test: true
        );
    }
}
