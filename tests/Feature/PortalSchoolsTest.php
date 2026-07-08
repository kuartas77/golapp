<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PortalSchoolsTest extends TestCase
{
    private array $temporaryUploads = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'recaptchav3.sitekey' => null,
            'recaptchav3.secret' => null,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryUploads as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_portal_school_index_only_lists_schools_with_enabled_inscriptions(): void
    {
        $visibleSchool = $this->createSchool([
            'name' => 'Escuela Visible',
            'slug' => 'escuela-visible',
            'email' => 'visible@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ]);

        $hiddenSchool = $this->createSchool([
            'name' => 'Escuela Oculta',
            'slug' => 'escuela-oculta',
            'email' => 'oculta@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => false,
        ]);

        $response = $this->getJson('/api/v2/portal/escuelas/data');

        $response->assertOk();
        $response->assertJsonPath('data.schools.0.slug', $visibleSchool['slug']);

        $listedSlugs = collect($response->json('data.schools'))->pluck('slug');

        $this->assertTrue($listedSlugs->contains($visibleSchool['slug']));
        $this->assertFalse($listedSlugs->contains($hiddenSchool['slug']));
    }

    public function test_portal_inscription_store_is_blocked_when_school_has_inscriptions_disabled(): void
    {
        $school = $this->createSchool([
            'name' => 'Escuela Sin Inscripciones',
            'slug' => 'escuela-sin-inscripciones',
            'email' => 'sin-inscripciones@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => false,
        ]);

        $response = $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school['slug']]),
            $this->portalInscriptionPayload($school['slug'])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['school_data']);
        $this->assertDatabaseCount('players', 0);
        $this->assertDatabaseCount('inscriptions', 0);
    }

    public function test_portal_inscription_store_is_blocked_when_school_reached_year_limit(): void
    {
        config([
            'recaptchav3.sitekey' => null,
            'recaptchav3.secret' => null,
        ]);

        $schoolData = $this->createSchool([
            'name' => 'Escuela Cupo Portal',
            'slug' => 'escuela-cupo-portal',
            'email' => 'cupo-portal@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ]);
        $school = School::query()->findOrFail($schoolData['id']);
        $school->settingsValues()
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->update(['value' => '1']);

        $existingPlayer = Player::factory()->create([
            'school_id' => $school->id,
        ]);
        Inscription::factory()->create([
            'player_id' => $existingPlayer->id,
            'unique_code' => $existingPlayer->unique_code,
            'school_id' => $school->id,
            'year' => now()->year,
            'competition_group_id' => null,
        ]);

        $response = $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload($school->slug)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['max_inscriptions']);
        $this->assertDatabaseMissing('players', [
            'identification_document' => '1002003004',
            'school_id' => $school->id,
        ]);
    }

    public function test_portal_school_data_reports_when_inscription_limit_is_reached(): void
    {
        $schoolData = $this->createSchool([
            'name' => 'Escuela Sin Cupos',
            'slug' => 'escuela-sin-cupos',
            'email' => 'sin-cupos@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ]);
        $school = School::query()->findOrFail($schoolData['id']);
        $school->settingsValues()
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->update(['value' => '1']);

        $player = Player::factory()->create(['school_id' => $school->id]);
        Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'school_id' => $school->id,
            'year' => now()->year,
            'competition_group_id' => null,
        ]);

        $this->getJson(route('api.v2.portal.school.show.data', [$school->slug]))
            ->assertOk()
            ->assertJsonPath('data.inscriptionLimit.current', 1)
            ->assertJsonPath('data.inscriptionLimit.limit', 1)
            ->assertJsonPath('data.inscriptionLimit.remaining', 0)
            ->assertJsonPath('data.inscriptionLimit.is_full', true);
    }

    public function test_portal_school_data_uses_same_origin_form_endpoints(): void
    {
        $school = $this->createSchool([
            'slug' => 'escuela-endpoints-relativos',
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ]);

        $this->getJson(route('api.v2.portal.school.show.data', [$school['slug']]))
            ->assertOk()
            ->assertJsonPath(
                'data.endpoints.store',
                route('api.v2.portal.school.inscription.store', [$school['slug']], false)
            )
            ->assertJsonPath(
                'data.endpoints.clientError',
                route('api.v2.portal.inscription.client-error', [], false)
            )
            ->assertJsonPath(
                'data.endpoints.autocomplete',
                route('api.v2.portal.autocomplete.fields', [], false)
            )
            ->assertJsonPath(
                'data.endpoints.searchDoc',
                route('api.v2.portal.autocomplete.search_doc', [], false)
            );
    }

    public function test_portal_accepts_sanitized_browser_failure_reports(): void
    {
        Log::spy();

        $this->postJson(route('api.v2.portal.inscription.client-error'), [
            'school_slug' => 'club-deportivo-academia-union',
            'endpoint' => '/api/v2/portal/club-deportivo-academia-union/inscripcion',
            'error_code' => 'ERR_NETWORK',
            'error_message' => 'Network Error',
            'status' => null,
            'online' => true,
            'file_sizes' => [
                'player_document' => 2000000,
                'medical_certificate' => 1800000,
            ],
            'total_file_bytes' => 3800000,
        ])->assertOk()->assertJsonPath('reported', true);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Portal inscription failed in browser', \Mockery::on(
                fn (array $context): bool => $context['school_slug'] === 'club-deportivo-academia-union'
                    && $context['error_code'] === 'ERR_NETWORK'
                    && $context['total_file_bytes'] === 3800000
                    && isset($context['ip'], $context['user_agent'])
            ));
    }

    public function test_portal_allows_registering_two_players_with_the_same_guardian(): void
    {
        config([
            'recaptchav3.sitekey' => null,
            'recaptchav3.secret' => null,
        ]);

        $schoolData = $this->createSchool([
            'name' => 'Escuela Acudiente Compartido',
            'slug' => 'escuela-acudiente-compartido',
            'email' => 'acudiente-compartido@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ]);
        $school = School::query()->findOrFail($schoolData['id']);

        $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload($school->slug)
        )->assertOk();

        $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            array_merge($this->portalInscriptionPayload($school->slug), [
                'names' => 'Segundo',
                'last_names' => 'Deportista',
                'identification_document' => '1002003005',
                'email' => 'segundo.deportista@example.com',
            ])
        )->assertOk();

        $guardian = People::query()
            ->where('identification_card', '900800700')
            ->where('email', 'acudiente.prueba@example.com')
            ->firstOrFail();

        $this->assertSame(2, $guardian->players()->count());
    }

    public function test_portal_rejects_guardian_email_registered_with_another_document(): void
    {
        config([
            'recaptchav3.sitekey' => null,
            'recaptchav3.secret' => null,
        ]);

        $schoolData = $this->createSchool([
            'name' => 'Escuela Correo Acudiente',
            'slug' => 'escuela-correo-acudiente',
            'email' => 'correo-acudiente@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ]);
        $school = School::query()->findOrFail($schoolData['id']);

        $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload($school->slug)
        )->assertOk();

        $response = $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            array_merge($this->portalInscriptionPayload($school->slug), [
                'names' => 'Otro',
                'last_names' => 'Acudido',
                'identification_document' => '1002003006',
                'email' => 'otro.acudido@example.com',
                'tutor_num_doc' => '900800701',
            ])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tutor_email']);
    }

    public function test_portal_accepts_real_jpeg_png_and_pdf_documents(): void
    {
        Notification::fake();
        Storage::fake('local');

        $school = $this->createSchool([
            'slug' => 'escuela-documentos-validos',
            'is_enable' => true,
            'inscriptions_enabled' => true,
            'send_documents' => true,
        ]);

        $response = $this->withHeader('Accept', 'application/json')->post(
            route('api.v2.portal.school.inscription.store', [$school['slug']]),
            array_merge($this->portalInscriptionPayload($school['slug']), $this->validPortalDocuments())
        );

        $response->assertOk();
        $this->assertDatabaseHas('players', [
            'identification_document' => '1002003004',
            'school_id' => $school['id'],
        ]);
    }

    public function test_portal_requires_documents_when_school_enables_document_uploads(): void
    {
        $school = $this->createSchool([
            'slug' => 'escuela-documentos-obligatorios',
            'is_enable' => true,
            'inscriptions_enabled' => true,
            'send_documents' => true,
        ]);

        $response = $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school['slug']]),
            $this->portalInscriptionPayload($school['slug'])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'player_document',
            'medical_certificate',
            'tutor_document',
        ]);
    }

    public function test_portal_rejects_renamed_content_that_is_not_a_real_pdf(): void
    {
        $school = $this->createSchool([
            'slug' => 'escuela-documento-renombrado',
            'is_enable' => true,
            'inscriptions_enabled' => true,
            'send_documents' => true,
        ]);
        $documents = $this->validPortalDocuments();
        $documents['player_document'] = $this->uploadedFileWithContent(
            'documento.pdf',
            'Este contenido no es un PDF.'
        );

        $response = $this->withHeader('Accept', 'application/json')->post(
            route('api.v2.portal.school.inscription.store', [$school['slug']]),
            array_merge($this->portalInscriptionPayload($school['slug']), $documents)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['player_document']);
    }

    public function test_portal_rejects_documents_larger_than_three_megabytes(): void
    {
        $school = $this->createSchool([
            'slug' => 'escuela-documento-pesado',
            'is_enable' => true,
            'inscriptions_enabled' => true,
            'send_documents' => true,
        ]);
        $documents = $this->validPortalDocuments();
        $documents['player_document'] = $this->fakePdf(
            'documento-pesado.pdf',
            (3 * 1024 * 1024) + 1
        );

        $response = $this->withHeader('Accept', 'application/json')->post(
            route('api.v2.portal.school.inscription.store', [$school['slug']]),
            array_merge($this->portalInscriptionPayload($school['slug']), $documents)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['player_document']);
    }

    private function portalInscriptionPayload(string $schoolSlug): array
    {
        return [
            'names' => 'Jugador',
            'last_names' => 'Prueba',
            'date_birth' => '2014-05-11',
            'place_birth' => 'Medellin',
            'identification_document' => '1002003004',
            'document_type' => 'TI',
            'gender' => 'M',
            'email' => 'jugador.prueba@example.com',
            'mobile' => '3001234567',
            'medical_history' => 'Ninguno',
            'school' => 'Colegio de prueba',
            'degree' => '6',
            'jornada' => 'Manana',
            'address' => 'Calle 1 # 2 - 3',
            'municipality' => 'Medellin',
            'neighborhood' => 'Laureles',
            'rh' => 'O+',
            'eps' => 'Sura',
            'student_insurance' => 'Seguro escolar',
            'tutor_name' => 'Acudiente Prueba',
            'tutor_num_doc' => '900800700',
            'tutor_doc_exp' => 'Medellin',
            'tutor_relationship' => 'Madre',
            'tutor_phone' => '3009876543',
            'tutor_work' => 'Empresa Demo',
            'tutor_position_held' => 'Analista',
            'tutor_email' => 'acudiente.prueba@example.com',
            'year' => now()->format('Y'),
            'slug' => $schoolSlug,
        ];
    }

    private function validPortalDocuments(): array
    {
        return [
            'player_document' => $this->fakePdf('documento-deportista.pdf'),
            'medical_certificate' => $this->fakeImage('certificado-medico.jpg', 'jpeg'),
            'tutor_document' => $this->fakeImage('documento-acudiente.png', 'png'),
        ];
    }

    private function fakePdf(string $name, int $minimumSize = 0): UploadedFile
    {
        $pdf = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF\n";

        if ($minimumSize > strlen($pdf)) {
            $pdf .= str_repeat(' ', $minimumSize - strlen($pdf));
        }

        return $this->uploadedFileWithContent($name, $pdf);
    }

    private function fakeImage(string $name, string $type): UploadedFile
    {
        $image = imagecreatetruecolor(10, 10);
        ob_start();

        $type === 'png' ? imagepng($image) : imagejpeg($image);

        $contents = (string) ob_get_clean();
        imagedestroy($image);

        return $this->uploadedFileWithContent($name, $contents);
    }

    private function uploadedFileWithContent(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'portal-upload-');

        file_put_contents($path, $contents);
        $this->temporaryUploads[] = $path;

        return new UploadedFile($path, $name, null, UPLOAD_ERR_OK, true);
    }
}
