<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\User;
use Tests\TestCase;

final class AttendanceQrTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function testAttendanceQrApiRequiresAuthentication(): void
    {
        $this->getJson('/api/v2/attendance-qr/QR-001')->assertUnauthorized();
        $this->postJson('/api/v2/attendance-qr/1/take', ['column' => 'assistance_one'])->assertUnauthorized();
    }

    public function testSchoolUserCanResolveAttendanceQrContext(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school);
        [$player, $inscription] = $this->createPlayerWithInscription($school, $group, now()->year);
        $assist = $this->createAssist($inscription, $group);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v2/attendance-qr/{$player->unique_code}")
            ->assertOk();

        $response
            ->assertJsonPath('unique_code', $player->unique_code)
            ->assertJsonPath('inscription_id', $inscription->id)
            ->assertJsonPath('assist_id', $assist->id)
            ->assertJsonPath('training_group.id', $group->id);

        $this->assertNotEmpty($response->json('class_days'));
    }

    public function testAttendanceQrUsesOnlyCurrentYearInscription(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school);
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => 'QR-OLD-' . fake()->unique()->numberBetween(1000, 9999),
        ]);

        Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'school_id' => $school->id,
            'year' => now()->year - 1,
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/attendance-qr/{$player->unique_code}")
            ->assertNotFound()
            ->assertJsonPath('message', 'No encontramos una inscripción vigente para este código en el año actual.');
    }

    public function testInstructorCanResolveAttendanceQrOnlyForAssignedGroup(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school);
        [$player] = $this->createPlayerWithInscription($school, $group, now()->year);
        $this->createAssist(Inscription::query()->where('unique_code', $player->unique_code)->where('year', now()->year)->firstOrFail(), $group);

        $instructor = $this->createSchoolScopedUser($school->id, ['instructor'], sprintf('attendance-qr-instructor-%s@example.com', uniqid()));
        $group->instructors()->attach($instructor->id, ['assigned_year' => now()->year]);

        $this->actingAs($instructor)
            ->getJson("/api/v2/attendance-qr/{$player->unique_code}")
            ->assertOk();

        $blockedInstructor = $this->createSchoolScopedUser($school->id, ['instructor'], sprintf('attendance-qr-blocked-%s@example.com', uniqid()));

        $this->actingAs($blockedInstructor)
            ->getJson("/api/v2/attendance-qr/{$player->unique_code}")
            ->assertForbidden();
    }

    public function testSuperAdminCanResolveAttendanceQrForSelectedSchool(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school);
        [$player, $inscription] = $this->createPlayerWithInscription($school, $group, now()->year);
        $this->createAssist($inscription, $group);

        $superAdmin = $this->createSchoolScopedUser(
            $school->id,
            ['super-admin'],
            sprintf('attendance-qr-admin-%s@example.com', uniqid())
        );

        $this->withSession(['admin.selected_school' => $school->id])
            ->actingAs($superAdmin)
            ->getJson("/api/v2/attendance-qr/{$player->unique_code}")
            ->assertOk();
    }

    public function testAttendanceQrTakeMarksAttendanceAsPresent(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school);
        [, $inscription] = $this->createPlayerWithInscription($school, $group, now()->year);
        $assist = $this->createAssist($inscription, $group, [
            'assistance_one' => 2,
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/v2/attendance-qr/{$assist->id}/take", [
                'column' => 'assistance_one',
            ])
            ->assertOk()
            ->assertJsonPath('saved', true)
            ->assertJsonPath('current_value', 1);

        $this->assertDatabaseHas('assists', [
            'id' => $assist->id,
            'assistance_one' => 1,
        ]);
    }

    public function testAttendanceQrTakeRejectsColumnsOutsideConfiguredClassDays(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school);
        [, $inscription] = $this->createPlayerWithInscription($school, $group, now()->year);
        $assist = $this->createAssist($inscription, $group);

        $this->actingAs($this->user)
            ->postJson("/api/v2/attendance-qr/{$assist->id}/take", [
                'column' => 'assistance_twenty_five',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'La clase seleccionada no corresponde a los días válidos del mes actual.');
    }

    private function createTrainingGroup(School $school): TrainingGroup
    {
        return TrainingGroup::query()->create([
            'name' => 'QR Team ' . fake()->unique()->numberBetween(100, 999),
            'year' => now()->year,
            'category' => ['Todas las categorías'],
            'days' => ['Lunes', 'Martes'],
            'schedules' => ['10:00AM - 11:00AM'],
            'school_id' => $school->id,
            'year_active' => now()->year,
        ]);
    }

    private function createPlayerWithInscription(School $school, TrainingGroup $group, int $year): array
    {
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => 'QR-' . fake()->unique()->numberBetween(1000, 9999),
        ]);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'school_id' => $school->id,
            'year' => $year,
            'start_date' => sprintf('%d-01-01', $year),
        ]);

        return [$player, $inscription];
    }

    private function createAssist(Inscription $inscription, TrainingGroup $group, array $overrides = []): Assist
    {
        return Assist::query()->updateOrCreate([
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => now()->month,
            'school_id' => $inscription->school_id,
        ], array_merge([
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => now()->month,
            'school_id' => $inscription->school_id,
        ], $overrides));
    }

    private function createSchoolScopedUser(int $schoolId, array $roles, string $email): User
    {
        $user = $this->createUser([
            'email' => $email,
            'school_id' => $schoolId,
        ], $roles);

        SchoolUser::query()->create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);

        return $user;
    }
}
