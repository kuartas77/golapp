<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\User;
use App\Notifications\RegisterNotification;
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

    public function testSuperAdminCanCreateRegularSchool(): void
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

        Notification::assertSentTo($user, RegisterNotification::class);
        $response->assertJsonPath('school.slug', $school->slug);
    }

    public function testSchoolDefaultsAreIdempotentWhenConfigDefaultRunsAgain(): void
    {
        $school = School::factory()->create([
            'email' => 'observer-idempotent@example.com',
            'slug' => 'observer-idempotent',
        ]);

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
    }

    public function testSuperAdminCanCreateCampusSchoolAndSyncMultipleSchoolsGroup(): void
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

    public function testSuperAdminCanFetchSchoolFormData(): void
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

    public function testSuperAdminCanUpdateSchoolAndResyncCampusGroup(): void
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

    public function testSuperAdminValidatesCampusCreationPayload(): void
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

    public function testSuperAdminValidatesMultipleSchoolIdsOnUpdate(): void
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
