<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Service\Category\CategoryConversionService;
use App\Service\Category\CategoryFormatService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SuperAdminSchoolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_super_admin_can_create_regular_school(): void
    {
        Notification::fake();
        Storage::fake('public');

        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $response = $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/admin/schools', [
                'name' => 'Escuela Regular Test',
                'agent' => 'Administradora Test',
                'address' => 'Calle 123',
                'phone' => '3001234567',
                'email' => 'regular-school@example.com',
                'is_enable' => '1',
                'is_campus' => false,
                'logo' => UploadedFile::fake()->image('logo.jpg'),
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $school = School::query()->firstWhere('slug', 'escuela-regular-test');

        $this->assertNotNull($school);

        $user = User::query()->firstWhere('email', 'regular-school@example.com');

        $this->assertNotNull($user);
        $this->assertSame($school->id, $user->school_id);

        $this->assertDatabaseHas('schools_user', [
            'school_id' => $school->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::MULTIPLE_SCHOOLS,
        ]);

        $this->assertCount(15, $school->schedules);
        $this->assertSame(1, $school->trainingGroups()->where('name', 'Provisional')->count());
        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::INSCRIPTION_AMOUNT,
            'value' => '70000',
        ]);
        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::MONTHLY_PAYMENT,
            'value' => '50000',
        ]);
        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::MAX_INSCRIPTIONS,
            'value' => '200',
        ]);

        Notification::assertSentTo(
            $user,
            RegisterNotification::class,
            function (RegisterNotification $notification) use ($school, $user): bool {
                $message = $notification->toMail($user);
                $this->assertSame([config('mail.from.address'), $school->name], $message->from);

                return true;
            },
        );
        $response->assertJsonPath('school.slug', $school->slug);
    }

    public function test_super_admin_can_configure_school_max_inscriptions(): void
    {
        Notification::fake();

        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/admin/schools', [
                'name' => 'Escuela Cupo Test',
                'agent' => 'Administradora Cupo',
                'address' => 'Calle 456',
                'phone' => '3001112233',
                'email' => 'cupo-school@example.com',
                'is_enable' => '1',
                'is_campus' => false,
                'max_inscriptions' => 75,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $school = School::query()->firstWhere('slug', 'escuela-cupo-test');

        $this->assertNotNull($school);
        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::MAX_INSCRIPTIONS,
            'value' => '75',
        ]);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post("/api/v2/admin/schools/{$school->slug}", [
                '_method' => 'PUT',
                'name' => $school->name,
                'agent' => 'Administradora Actualizada',
                'address' => 'Calle 789',
                'phone' => '3004445566',
                'email' => $school->email,
                'is_enable' => '1',
                'is_campus' => false,
                'max_inscriptions' => 125,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::MAX_INSCRIPTIONS,
            'value' => '125',
        ]);
    }

    public function test_super_admin_can_configure_platform_options(): void
    {
        Notification::fake();

        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/admin/schools', [
                'name' => 'Escuela Plataforma Test',
                'agent' => 'Administradora Plataforma',
                'address' => 'Calle 654',
                'phone' => '3009876543',
                'email' => 'platform-school@example.com',
                'is_enable' => '1',
                'is_campus' => false,
                'inscriptions_enabled' => '1',
                'tutor_platform' => '1',
                'sign_player' => '1',
                'create_contract' => '1',
                'send_documents' => '1',
                'send_monthly_payment_receipts' => '1',
                'send_debt_notifications' => '1',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $school = School::query()->firstWhere('slug', 'escuela-plataforma-test');

        $this->assertNotNull($school);
        $this->assertTrue($school->inscriptions_enabled);
        $this->assertTrue($school->tutor_platform);
        $this->assertTrue($school->sign_player);
        $this->assertTrue($school->create_contract);
        $this->assertTrue($school->send_documents);
        $this->assertTrue($school->send_monthly_payment_receipts);
        $this->assertTrue($school->send_debt_notifications);

        $response = $this->actingAs($superAdmin)
            ->getJson("/api/v2/admin/schools/{$school->slug}")
            ->assertOk();

        $response->assertJsonPath('school.inscriptions_enabled', true)
            ->assertJsonPath('school.tutor_platform', true)
            ->assertJsonPath('school.sign_player', true)
            ->assertJsonPath('school.create_contract', true)
            ->assertJsonPath('school.send_documents', true)
            ->assertJsonPath('school.send_monthly_payment_receipts', true)
            ->assertJsonPath('school.send_debt_notifications', true);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post("/api/v2/admin/schools/{$school->slug}", [
                '_method' => 'PUT',
                'name' => $school->name,
                'agent' => $school->agent,
                'address' => $school->address,
                'phone' => $school->phone,
                'email' => $school->email,
                'is_enable' => '1',
                'is_campus' => false,
                'inscriptions_enabled' => '0',
                'tutor_platform' => '0',
                'sign_player' => '0',
                'create_contract' => '0',
                'send_documents' => '0',
                'send_monthly_payment_receipts' => '0',
                'send_debt_notifications' => '0',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $school->refresh();

        $this->assertFalse($school->inscriptions_enabled);
        $this->assertFalse($school->tutor_platform);
        $this->assertFalse($school->sign_player);
        $this->assertFalse($school->create_contract);
        $this->assertFalse($school->send_documents);
        $this->assertFalse($school->send_monthly_payment_receipts);
        $this->assertFalse($school->send_debt_notifications);
    }

    public function test_group_monthly_payment_option_can_be_enabled_before_groups_have_tariffs(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $school = School::query()->findOrFail($this->school['id']);
        TrainingGroup::query()->create([
            'school_id' => $school->id,
            'name' => 'Grupo sin tarifa',
            'year_active' => now()->year,
            'is_complementary' => false,
        ]);
        TrainingGroup::query()->create([
            'school_id' => $school->id,
            'name' => 'Complementario sin tarifa',
            'year_active' => now()->year,
            'is_complementary' => true,
        ]);
        TrainingGroup::query()->create([
            'school_id' => $school->id,
            'name' => 'Grupo anterior sin tarifa',
            'year_active' => now()->year - 1,
            'is_complementary' => false,
        ]);

        $payload = [
            '_method' => 'PUT',
            'name' => $school->name,
            'agent' => $school->agent,
            'address' => $school->address,
            'phone' => $school->phone,
            'email' => $school->email,
            'is_enable' => '1',
            'is_campus' => false,
            'training_group_monthly_payment_enabled' => '1',
        ];

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post("/api/v2/admin/schools/{$school->slug}", $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($school->fresh()->training_group_monthly_payment_enabled);
    }

    public function test_super_admin_can_choose_group_pricing_when_creating_a_school(): void
    {
        Notification::fake();

        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/admin/schools', [
                'name' => 'Escuela Tarifa Grupos',
                'agent' => 'Administradora Tarifas',
                'address' => 'Calle 987',
                'phone' => '3009998877',
                'email' => 'group-pricing-school@example.com',
                'is_enable' => '1',
                'is_campus' => false,
                'training_group_monthly_payment_enabled' => '1',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $school = School::query()->firstWhere('slug', 'escuela-tarifa-grupos');

        $this->assertNotNull($school);
        $this->assertTrue($school->training_group_monthly_payment_enabled);
        $this->assertDatabaseHas('training_groups', [
            'school_id' => $school->id,
            'name' => 'Provisional',
            'monthly_payment_amount' => null,
        ]);
    }

    public function test_super_admin_can_change_category_format_and_convert_existing_data(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 12));
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $school = School::query()->findOrFail($this->school['id']);
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
            'name' => 'Formativo super-admin',
            'school_id' => $school->id,
            'year_active' => 2026,
            'category' => ['SUB-9', 'SUB-10'],
        ]);
        $tournament = Tournament::query()->create([
            'name' => 'Copa Formatos Super Admin',
            'school_id' => $school->id,
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Competencia Formatos Super Admin',
            'year' => 'SUB-9',
            'category' => 'SUB-9',
            'categories' => ['SUB-9', 'SUB-10'],
            'tournament_id' => $tournament->id,
            'user_id' => $superAdmin->id,
            'school_id' => $school->id,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/api/v2/admin/schools/{$school->slug}", $this->superAdminSchoolPayload($school, [
                'category_format' => CategoryFormatService::BIRTH_YEAR,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('CAT-2017', $player->fresh()->category);
        $this->assertSame('CAT-2017', Inscription::query()->withTrashed()->findOrFail($inscription->id)->category);
        $this->assertSame(['CAT-2017', 'CAT-2016'], $trainingGroup->fresh()->category);
        $this->assertSame(['CAT-2017', 'CAT-2016'], $competitionGroup->fresh()->categories);
        $this->assertSame('CAT-2017, CAT-2016', $competitionGroup->fresh()->category);
        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::CATEGORY_FORMAT,
            'value' => CategoryFormatService::BIRTH_YEAR,
        ]);

        $this->actingAs($superAdmin)
            ->getJson("/api/v2/admin/schools/{$school->slug}")
            ->assertOk()
            ->assertJsonPath('school.category_format', CategoryFormatService::BIRTH_YEAR);
    }

    public function test_super_admin_category_format_change_is_validated_and_transactional(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $school = School::query()->findOrFail($this->school['id']);

        $this->mock(CategoryConversionService::class)
            ->shouldReceive('convertSchool')
            ->once()
            ->andThrow(new \RuntimeException('Conversion failed'));

        $this->actingAs($superAdmin)
            ->putJson("/api/v2/admin/schools/{$school->slug}", $this->superAdminSchoolPayload($school, [
                'category_format' => 'custom',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_format');

        $this->actingAs($superAdmin)
            ->putJson("/api/v2/admin/schools/{$school->slug}", $this->superAdminSchoolPayload($school, [
                'category_format' => CategoryFormatService::BIRTH_YEAR,
            ]))
            ->assertServerError();

        $this->assertDatabaseHas('setting_values', [
            'school_id' => $school->id,
            'setting_key' => Setting::CATEGORY_FORMAT,
            'value' => CategoryFormatService::SUB_AGE,
        ]);
    }

    public function test_school_defaults_are_idempotent_when_config_default_runs_again(): void
    {
        $school = School::factory()->create([
            'email' => 'observer-idempotent@example.com',
            'slug' => 'observer-idempotent',
        ]);

        $school->settingsValues()
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->update(['value' => '85']);

        $school->configDefault();
        $school->refresh();

        $this->assertCount(15, $school->schedules);
        $this->assertSame(1, $school->trainingGroups()->where('name', 'Provisional')->count());
        $this->assertSame(
            count(collect(SettingValue::settingsDefault($school->id))->pluck('setting_key')->unique()),
            $school->settingsValues()->whereIn(
                'setting_key',
                collect(SettingValue::settingsDefault($school->id))->pluck('setting_key')->all()
            )->count()
        );
        $this->assertSame('85', (string) $school->settingsValues()
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->value('value'));
    }

    public function test_super_admin_can_create_campus_school_and_sync_multiple_schools_group(): void
    {
        Notification::fake();

        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::query()->findOrFail($this->createSchool([
            'email' => 'secondary-campus@example.com',
            'slug' => 'secondary-campus',
        ])['id']);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/admin/schools', [
                'name' => 'Nueva Sede Test',
                'agent' => 'Agente Campus',
                'address' => 'Carrera 45',
                'phone' => '3007654321',
                'email' => $this->user->email,
                'is_enable' => '1',
                'is_campus' => true,
                'multiple_schools' => [$this->school['id'], $secondarySchool->id],
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $newSchool = School::query()->firstWhere('slug', 'nueva-sede-test');

        $this->assertNotNull($newSchool);
        $this->assertSame($newSchool->id, $this->user->fresh()->school_id);

        $expectedGroup = [$this->school['id'], $secondarySchool->id, $newSchool->id];
        sort($expectedGroup);

        foreach ([$this->school['id'], $secondarySchool->id, $newSchool->id] as $schoolId) {
            $storedGroup = $this->multipleSchoolsGroupFor($schoolId);
            sort($storedGroup);

            $this->assertSame($expectedGroup, $storedGroup);
        }

        Notification::assertNothingSent();
    }

    public function test_super_admin_can_fetch_school_form_data(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::query()->findOrFail($this->createSchool([
            'email' => 'fetch-campus@example.com',
            'slug' => 'fetch-campus',
        ])['id']);

        $this->storeMultipleSchoolsGroup([$this->school['id'], $secondarySchool->id]);

        $response = $this->actingAs($superAdmin)
            ->getJson("/api/v2/admin/schools/{$this->school['slug']}")
            ->assertOk();

        $this->assertTrue($response->json('school.is_campus'));
        $this->assertSame([$secondarySchool->id], $response->json('multiple_schools'));
        $this->assertNotContains($this->school['id'], array_column($response->json('schools'), 'value'));
    }

    public function test_super_admin_can_update_school_and_resync_campus_group(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::query()->findOrFail($this->createSchool([
            'email' => 'update-secondary@example.com',
            'slug' => 'update-secondary',
        ])['id']);
        $thirdSchool = School::query()->findOrFail($this->createSchool([
            'email' => 'update-third@example.com',
            'slug' => 'update-third',
        ])['id']);

        $this->storeMultipleSchoolsGroup([$this->school['id'], $secondarySchool->id]);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post("/api/v2/admin/schools/{$this->school['slug']}", [
                '_method' => 'PUT',
                'name' => $this->school['name'],
                'agent' => 'Nuevo agente',
                'address' => 'Nueva dirección',
                'phone' => '3200000000',
                'email' => $this->school['email'],
                'is_enable' => '0',
                'is_campus' => true,
                'multiple_schools' => [$thirdSchool->id],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $school = School::query()->findOrFail($this->school['id']);

        $this->assertSame('Nuevo agente', $school->agent);
        $this->assertSame('Nueva dirección', $school->address);
        $this->assertSame('3200000000', $school->phone);
        $this->assertFalse($school->is_enable);

        $expectedGroup = [$this->school['id'], $thirdSchool->id];
        sort($expectedGroup);

        foreach ([$this->school['id'], $thirdSchool->id] as $schoolId) {
            $storedGroup = $this->multipleSchoolsGroupFor($schoolId);
            sort($storedGroup);

            $this->assertSame($expectedGroup, $storedGroup);
        }

        $this->assertDatabaseMissing('setting_values', [
            'school_id' => $secondarySchool->id,
            'setting_key' => Setting::MULTIPLE_SCHOOLS,
        ]);
    }

    public function test_super_admin_can_update_legacy_slug_school_and_remove_campus_group(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);
        $secondarySchool = School::query()->findOrFail($this->createSchool([
            'email' => 'remove-campus@example.com',
            'slug' => 'remove-campus',
        ])['id']);
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill(['slug' => 'slug-historico-personalizado'])->save();
        $this->storeMultipleSchoolsGroup([$school->id, $secondarySchool->id]);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post("/api/v2/admin/schools/{$school->slug}", [
                '_method' => 'PUT',
                'name' => $school->name,
                'agent' => 'Agente sin sedes',
                'address' => $school->address,
                'phone' => $school->phone,
                'email' => $school->email,
                'is_enable' => '1',
                'is_campus' => '0',
                'multiple_schools' => [],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Agente sin sedes', $school->fresh()->agent);
        foreach ([$school->id, $secondarySchool->id] as $schoolId) {
            $this->assertDatabaseMissing('setting_values', [
                'school_id' => $schoolId,
                'setting_key' => Setting::MULTIPLE_SCHOOLS,
            ]);
        }
    }

    public function test_super_admin_validates_campus_creation_payload(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v2/admin/schools', [
                'name' => 'Campus inválido',
                'agent' => 'Administrador',
                'address' => 'Carrera falsa',
                'phone' => '3000000000',
                'email' => 'missing-user@example.com',
                'is_enable' => '1',
                'is_campus' => true,
                'multiple_schools' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'multiple_schools']);
    }

    public function test_super_admin_validates_multiple_school_ids_on_update(): void
    {
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $this->actingAs($superAdmin)
            ->withHeader('Accept', 'application/json')
            ->post("/api/v2/admin/schools/{$this->school['slug']}", [
                '_method' => 'PUT',
                'name' => $this->school['name'],
                'agent' => $this->school['agent'],
                'address' => $this->school['address'],
                'phone' => $this->school['phone'],
                'email' => $this->school['email'],
                'is_enable' => '1',
                'is_campus' => true,
                'multiple_schools' => [999999, 999999],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['multiple_schools.0', 'multiple_schools.1']);
    }

    private function createSuperAdminForSchool(int $schoolId): User
    {
        $user = $this->createUser([
            'email' => sprintf('superadmin-%s@example.com', uniqid()),
            'school_id' => $schoolId,
        ], ['super-admin']);

        SchoolUser::query()->create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);

        return $user;
    }

    private function superAdminSchoolPayload(School $school, array $overrides = []): array
    {
        return array_merge([
            'name' => $school->name,
            'agent' => $school->agent,
            'address' => $school->address,
            'phone' => $school->phone,
            'email' => $school->email,
            'is_enable' => $school->is_enable,
            'is_campus' => false,
            'category_format' => CategoryFormatService::SUB_AGE,
        ], $overrides);
    }

    private function multipleSchoolsGroupFor(int $schoolId): array
    {
        return json_decode((string) SettingValue::query()
            ->where('school_id', $schoolId)
            ->where('setting_key', Setting::MULTIPLE_SCHOOLS)
            ->value('value'), true) ?? [];
    }

    private function storeMultipleSchoolsGroup(array $schoolIds): void
    {
        foreach ($schoolIds as $schoolId) {
            SettingValue::query()->updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'setting_key' => Setting::MULTIPLE_SCHOOLS,
                ],
                [
                    'value' => json_encode($schoolIds, JSON_THROW_ON_ERROR),
                ]
            );
        }
    }
}
