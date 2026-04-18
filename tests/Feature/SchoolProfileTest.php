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
            ->putJson("/api/v2/admin/school/{$school->slug}", [
                'name' => $school->name,
                'email' => $school->email,
                'agent' => $school->agent,
                'address' => $school->address,
                'phone' => $school->phone,
                'NOTIFY_PAYMENT_DAY' => '16',
                'INSCRIPTION_AMOUNT' => '70000',
                'MONTHLY_PAYMENT' => '50000',
                'BROTHER_MONTHLY_PAYMENT' => '65000',
                'ANNUITY' => '48333',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::BROTHER_MONTHLY_PAYMENT,
            'value' => '65000',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('settings.BROTHER_MONTHLY_PAYMENT', '65000');
    }
}
