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
        $this->assertSame([
            'value' => 'M',
            'label' => 'Masculino',
        ], $response->json('genders.0'));

        $this->assertIsArray($response->json('relationships'));
        $this->assertSame([
            'value' => '30',
            'label' => 'ACUDIENTE',
        ], collect($response->json('relationships'))->firstWhere('value', '30'));

        $this->assertIsArray($response->json('type_payments'));
        $this->assertSame([
            'value' => '0',
            'label' => 'Pendiente',
        ], collect($response->json('type_payments'))->firstWhere('value', '0'));
    }
}
