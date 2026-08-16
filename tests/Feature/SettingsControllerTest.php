<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SettingsControllerTest extends TestCase
{
    public function test_general_settings_returns_config_options_as_arrays(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/settings/general');

        $response->assertOk();

        $this->assertIsArray($response->json('genders'));
        $this->assertContains('Volante (Primera línea)', collect($response->json('positions'))->pluck('name'));
        $this->assertNotContains('Defensa (Derecho)(Izquierdo)', collect($response->json('positions'))->pluck('name'));
        $this->assertNotContains('Volante (Primera linea)', collect($response->json('positions'))->pluck('name'));
        $this->assertNotContains('Volante (Segunda linea)', collect($response->json('positions'))->pluck('name'));
        $this->assertNotContains('Volante (Extremo)', collect($response->json('positions'))->pluck('name'));
        $this->assertNotContains('Delantero', collect($response->json('positions'))->pluck('name'));

        $this->assertSame([
            'value' => 'M',
            'label' => 'Masculino',
        ], $response->json('genders.0'));

        $this->assertIsArray($response->json('relationships'));
        $this->assertSame([
            ['value' => '15', 'label' => 'MADRE'],
            ['value' => '20', 'label' => 'PADRE'],
            ['value' => '1', 'label' => 'ABUELA'],
            ['value' => '2', 'label' => 'ABUELO'],
            ['value' => '11', 'label' => 'HERMANA'],
            ['value' => '12', 'label' => 'HERMANO'],
            ['value' => '26', 'label' => 'TÍA'],
            ['value' => '27', 'label' => 'TÍO'],
            ['value' => '10', 'label' => 'OTRO FAMILIAR'],
            ['value' => '30', 'label' => 'ACUDIENTE'],
        ], $response->json('relationships'));

        $this->assertIsArray($response->json('type_payments'));
        $this->assertSame([
            'value' => '0',
            'label' => 'Pendiente',
        ], collect($response->json('type_payments'))->firstWhere('value', '0'));

        $this->assertSame([
            'value' => 'AUTOMATISMOS',
            'label' => 'AUTOMATISMOS',
        ], collect($response->json('training_session_tasks'))->firstWhere('value', 'AUTOMATISMOS'));

        $this->assertSame([
            'value' => 'ATAQUE ORGANIZADO',
            'label' => 'ATAQUE ORGANIZADO',
        ], collect($response->json('training_session_general_objectives'))->firstWhere('value', 'ATAQUE ORGANIZADO'));

        $this->assertSame([
            'value' => 'SALIDA DE BALÓN',
            'label' => 'SALIDA DE BALÓN',
        ], collect($response->json('training_session_specific_goals'))->firstWhere('value', 'SALIDA DE BALÓN'));

        $this->assertSame([
            'value' => 'PASE',
            'label' => 'PASE',
        ], collect($response->json('training_session_contents'))->firstWhere('value', 'PASE'));
    }
}
