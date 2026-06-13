<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use Tests\TestCase;

final class PortalSchoolsTest extends TestCase
{
    public function testPortalSchoolIndexOnlyListsSchoolsWithEnabledInscriptions(): void
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

    public function testPortalInscriptionStoreIsBlockedWhenSchoolHasInscriptionsDisabled(): void
    {
        $school = $this->createSchool([
            'name' => 'Escuela Sin Inscripciones',
            'slug' => 'escuela-sin-inscripciones',
            'email' => 'sin-inscripciones@example.com',
            'is_enable' => true,
            'inscriptions_enabled' => false,
        ]);

        $response = $this->postJson(
            route('portal.school.inscription.store', [$school['slug']]),
            $this->portalInscriptionPayload($school['slug'])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['school_data']);
        $this->assertDatabaseCount('players', 0);
        $this->assertDatabaseCount('inscriptions', 0);
    }

    public function testPortalInscriptionStoreIsBlockedWhenSchoolReachedYearLimit(): void
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
            route('portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload($school->slug)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['max_inscriptions']);
        $this->assertDatabaseMissing('players', [
            'identification_document' => '1002003004',
            'school_id' => $school->id,
        ]);
    }

    public function testPortalAllowsRegisteringTwoPlayersWithTheSameGuardian(): void
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
            route('portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload($school->slug)
        )->assertOk();

        $this->postJson(
            route('portal.school.inscription.store', [$school->slug]),
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

    public function testPortalRejectsGuardianEmailRegisteredWithAnotherDocument(): void
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
            route('portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload($school->slug)
        )->assertOk();

        $response = $this->postJson(
            route('portal.school.inscription.store', [$school->slug]),
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
}
