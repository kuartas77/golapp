<?php

declare(strict_types=1);

namespace Tests\Feature;

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
