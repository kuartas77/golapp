<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\Setting;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SchoolProfileTest extends TestCase
{
    public function test_school_profile_update_persists_brother_monthly_payment(): void
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

    public function test_school_profile_cannot_update_super_admin_only_options(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                'tutor_platform' => 'true',
                'inscriptions_enabled' => 'true',
                'send_monthly_payment_receipts' => 'true',
                'send_debt_notifications' => 'true',
                Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED => true,
                Setting::CATEGORY_FORMAT => 'birth_year',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'tutor_platform',
                'inscriptions_enabled',
                'send_monthly_payment_receipts',
                'send_debt_notifications',
                Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED,
                Setting::CATEGORY_FORMAT,
            ]);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'tutor_platform' => false,
            'inscriptions_enabled' => false,
            'send_monthly_payment_receipts' => false,
            'send_debt_notifications' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('tutor_platform', false)
            ->assertJsonPath('inscriptions_enabled', false)
            ->assertJsonPath('send_monthly_payment_receipts', false)
            ->assertJsonPath('send_debt_notifications', false);
    }

    public function test_school_profile_update_does_not_query_loaded_settings_individually(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $individualSettingQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$individualSettingQueries): void {
            $sql = str_replace(['`', '"'], '', strtolower($query->sql));

            if (str_contains($sql, 'from setting_values') && str_contains($sql, 'setting_key = ?')) {
                $individualSettingQueries++;
            }
        });

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, $individualSettingQueries);
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
