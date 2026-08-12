<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Service\Category\CategoryConversionService;
use App\Service\Category\CategoryFormatService;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SchoolProfileTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

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

    public function test_school_profile_update_persists_platform_options(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                'tutor_platform' => 'true',
                'inscriptions_enabled' => 'true',
                'send_monthly_payment_receipts' => 'true',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'tutor_platform' => true,
            'inscriptions_enabled' => true,
            'send_monthly_payment_receipts' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('tutor_platform', true)
            ->assertJsonPath('inscriptions_enabled', true)
            ->assertJsonPath('send_monthly_payment_receipts', true);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                'tutor_platform' => 'false',
                'inscriptions_enabled' => 'false',
                'send_monthly_payment_receipts' => 'false',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'tutor_platform' => false,
            'inscriptions_enabled' => false,
            'send_monthly_payment_receipts' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/school')
            ->assertOk()
            ->assertJsonPath('tutor_platform', false)
            ->assertJsonPath('inscriptions_enabled', false)
            ->assertJsonPath('send_monthly_payment_receipts', false);
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
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                Setting::INSTRUCTOR_MONTHLY_EDIT_LOCK_ENABLED => true,
                Setting::CATEGORY_FORMAT => CategoryFormatService::SUB_AGE,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, $individualSettingQueries);
    }

    public function test_changing_category_format_converts_existing_school_data_only(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 12));
        $school = School::query()->findOrFail($this->school['id']);
        $this->assertSame(CategoryFormatService::SUB_AGE, $school->settings->get(Setting::CATEGORY_FORMAT));
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'date_birth' => '2017-03-10',
            'category' => 'SUB-9',
        ]);
        $inscription = Inscription::factory()->create([
            'school_id' => $school->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => TrainingGroup::query()->where('school_id', $school->id)->value('id'),
            'competition_group_id' => null,
            'year' => 2025,
            'category' => 'SUB-9',
        ]);
        $inscription->delete();
        $trainingGroup = TrainingGroup::query()->create([
            'name' => 'Formativo',
            'school_id' => $school->id,
            'year_active' => 2026,
            'category' => ['SUB-9', 'SUB-10'],
        ]);
        $tournament = Tournament::query()->create(['name' => 'Copa Formatos', 'school_id' => $school->id]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Competencia Formatos',
            'year' => 'SUB-9',
            'category' => 'SUB-9',
            'categories' => ['SUB-9', 'SUB-10'],
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'school_id' => $school->id,
        ]);

        $otherSchool = School::query()->findOrFail($this->createSchool(['email' => 'other-format@example.com'])['id']);
        $otherPlayer = Player::factory()->create([
            'school_id' => $otherSchool->id,
            'date_birth' => '2017-03-10',
            'category' => 'SUB-9',
        ]);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                Setting::CATEGORY_FORMAT => CategoryFormatService::BIRTH_YEAR,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Categoria-2017', $player->fresh()->category);
        $this->assertSame('Categoria-2017', Inscription::query()->withTrashed()->findOrFail($inscription->id)->category);
        $this->assertSame(['Categoria-2017', 'Categoria-2016'], $trainingGroup->fresh()->category);
        $this->assertSame(['Categoria-2017', 'Categoria-2016'], $competitionGroup->fresh()->categories);
        $this->assertSame('Categoria-2017, Categoria-2016', $competitionGroup->fresh()->category);
        $this->assertSame('SUB-9', $otherPlayer->fresh()->category);
        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::CATEGORY_FORMAT,
            'value' => CategoryFormatService::BIRTH_YEAR,
        ]);

        $catalogResponse = $this->actingAs($this->user)
            ->getJson('/api/v2/settings/groups')
            ->assertOk();
        $this->assertStringStartsWith('Categoria-', $catalogResponse->json('categories.0.id'));
    }

    public function test_category_format_change_rolls_back_when_conversion_fails(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $this->mock(CategoryConversionService::class)
            ->shouldReceive('convertSchool')
            ->once()
            ->andThrow(new \RuntimeException('Conversion failed'));

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                Setting::CATEGORY_FORMAT => CategoryFormatService::BIRTH_YEAR,
            ]))
            ->assertOk()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::CATEGORY_FORMAT,
            'value' => CategoryFormatService::SUB_AGE,
        ]);
    }

    public function test_category_format_rejects_unknown_values(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $this->actingAs($this->user)
            ->putJson("/api/v2/admin/school/{$school->slug}", $this->schoolProfilePayload($school, [
                Setting::CATEGORY_FORMAT => 'custom',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(Setting::CATEGORY_FORMAT);
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
            Setting::CATEGORY_FORMAT => CategoryFormatService::SUB_AGE,
        ], $overrides);
    }
}
