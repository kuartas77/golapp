<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\Setting;
use Tests\TestCase;

final class SchoolProfileTest extends TestCase
{
    public function testSchoolProfileUpdatePersistsBrotherMonthlyPayment(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                'BROTHER_MONTHLY_PAYMENT' => '65000',
                'MONTHLY_PAYMENT_OPTION_1' => '55000',
                'MONTHLY_PAYMENT_OPTION_2' => '60000',
                'MONTHLY_PAYMENT_OPTION_3' => '70000',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::BROTHER_MONTHLY_PAYMENT,
            'value' => '65000',
        ]);

        foreach ([
            Setting::MONTHLY_PAYMENT_OPTION_1 => '55000',
            Setting::MONTHLY_PAYMENT_OPTION_2 => '60000',
            Setting::MONTHLY_PAYMENT_OPTION_3 => '70000',
        ] as $setting => $value) {
            $this->assertDatabaseHas('setting_values', [
                'school_id' => $school->id,
                'setting_key' => $setting,
                'value' => $value,
            ]);
        }

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('settings.BROTHER_MONTHLY_PAYMENT', '65000')
            ->assertJsonPath('settings.MONTHLY_PAYMENT_OPTION_1', '55000')
            ->assertJsonPath('settings.MONTHLY_PAYMENT_OPTION_2', '60000')
            ->assertJsonPath('settings.MONTHLY_PAYMENT_OPTION_3', '70000');
    }

    public function testSchoolProfileUpdatePersistsPlatformOptions(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                'tutor_platform' => 'true',
                'inscriptions_enabled' => 'true',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'tutor_platform' => true,
            'inscriptions_enabled' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('tutor_platform', true)
            ->assertJsonPath('inscriptions_enabled', true);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                'tutor_platform' => 'false',
                'inscriptions_enabled' => 'false',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'tutor_platform' => false,
            'inscriptions_enabled' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('tutor_platform', false)
            ->assertJsonPath('inscriptions_enabled', false);
    }

    private function schoolProfilePayload(School $school, array $overrides = []): array
    {
        return array_merge([
                'name' => $school->name,
                'email' => $school->email,
                'agent' => $school->agent,
                'address' => $school->address,
                'phone' => $school->phone,
                'NOTIFY_PAYMENT_DAY' => '16',
                'INSCRIPTION_AMOUNT' => '70000',
                'MONTHLY_PAYMENT' => '50000',
                'BROTHER_MONTHLY_PAYMENT' => '65000',
                'MONTHLY_PAYMENT_OPTION_1' => '55000',
                'MONTHLY_PAYMENT_OPTION_2' => '60000',
                'MONTHLY_PAYMENT_OPTION_3' => '70000',
                'ANNUITY' => '48333',
            ], $overrides);
    }
}
