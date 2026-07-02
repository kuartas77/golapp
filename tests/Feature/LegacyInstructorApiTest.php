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
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class LegacyInstructorApiTest extends TestCase
{
    public function testProtectedRoutesRejectGuests(): void
    {
        foreach ([
            ['postJson', '/api/logout'],
            ['postJson', '/api/refresh-token'],
            ['getJson', '/api/check'],
            ['getJson', '/api/user'],
            ['getJson', '/api/img/dynamic/school/player.png'],
            ['getJson', '/api/instructor/training_groups'],
            ['getJson', '/api/instructor/training_groups/1'],
            ['getJson', '/api/instructor/statistics/groups'],
            ['getJson', '/api/instructor/attendances'],
            ['postJson', '/api/instructor/attendances/upsert'],
        ] as [$method, $uri]) {
            $this->{$method}($uri)->assertUnauthorized();
        }
    }

    public function testLoginUserCheckRefreshAndLogoutFlow(): void
    {
        [, $instructor] = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        $login = $this->login($instructor)
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $instructor->email);

        $accessToken = $login->json('access_token');
        $refreshToken = $login->json('refresh_token');

        $this->withToken($accessToken)
            ->getJson('/api/check')
            ->assertOk();

        $this->withToken($accessToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', $instructor->email);

        $refreshed = $this->withToken($refreshToken)
            ->postJson('/api/refresh-token')
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer');

        $newAccessToken = $refreshed->json('access_token');
        $this->app['auth']->forgetGuards();
        $this->withToken($accessToken)->getJson('/api/user')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($newAccessToken)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->app['auth']->forgetGuards();
        $this->withToken($newAccessToken)->getJson('/api/user')->assertUnauthorized();
    }

    public function testDynamicImageEndpointServesOnlyAuthenticatedAllowedImages(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('school/player.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));

        $token = $this->login($this->user)->json('access_token');

        $this->withToken($token)
            ->get('/api/img/dynamic/school/player.png')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->withToken($token)
            ->get('/api/img/dynamic/../.env')
            ->assertNotFound();
    }

    public function testInstructorCanListShowAndGetStatisticsForAssignedGroups(): void
    {
        [$instructor, $group] = $this->instructorWithAssignedGroup();
        $this->createInscription($group);
        $token = $this->login($instructor)->json('access_token');

        $this->withToken($token)
            ->getJson('/api/instructor/training_groups')
            ->assertOk()
            ->assertJsonPath('data.0.id', $group->id);

        $this->withToken($token)
            ->getJson("/api/instructor/training_groups/{$group->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $group->id);

        $this->withToken($token)
            ->getJson('/api/instructor/statistics/groups')
            ->assertOk()
            ->assertJsonPath('data.0.group_id', $group->id)
            ->assertJsonPath('data.0.attendances_total', 0);
    }

    public function testInstructorCanListAndUpdateAttendanceForAssignedGroup(): void
    {
        [$instructor, $group] = $this->instructorWithAssignedGroup();
        $inscription = $this->createInscription($group);
        Assist::query()->firstOrCreate([
            'school_id' => $group->school_id,
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => now()->month,
        ]);
        $token = $this->login($instructor)->json('access_token');

        $query = http_build_query([
            'training_group_id' => $group->id,
            'month' => now()->month,
            'year' => now()->year,
            'column' => 'assistance_one',
        ]);

        $this->withToken($token)
            ->getJson("/api/instructor/attendances?{$query}")
            ->assertOk()
            ->assertJsonPath('data.0.inscription_id', $inscription->id);

        $this->withToken($token)
            ->postJson('/api/instructor/attendances/upsert', [
                'group_id' => $group->id,
                'inscription_id' => $inscription->id,
                'month' => now()->month,
                'year' => now()->year,
                'column' => 'assistance_one',
                'value' => 'as',
            ])
            ->assertOk()
            ->assertJsonPath('data', true);

        $this->assertDatabaseHas('assists', [
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => now()->month,
            'assistance_one' => 1,
        ]);
    }

    private function login(User $user): TestResponse
    {
        return $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }

    /** @return array{User, TrainingGroup} */
    private function instructorWithAssignedGroup(): array
    {
        $school = School::findOrFail($this->school['id']);
        $instructor = $this->createUser([
            'school_id' => $school->id,
            'email' => sprintf('legacy-api-instructor-%s@example.com', uniqid()),
        ], [User::INSTRUCTOR]);
        SchoolUser::query()->create([
            'school_id' => $school->id,
            'user_id' => $instructor->id,
        ]);

        $group = $school->trainingGroups()->where('name', 'Provisional')->firstOrFail();
        $group->update(['year_active' => now()->year]);
        $group->instructors()->attach($instructor->id, ['assigned_year' => now()->year]);

        return [$instructor, $group->fresh()];
    }

    private function createInscription(TrainingGroup $group): Inscription
    {
        $player = Player::factory()->create([
            'school_id' => $group->school_id,
            'unique_code' => 'LEGACY-'.fake()->unique()->numberBetween(1000, 9999),
        ]);

        return Inscription::factory()->create([
            'school_id' => $group->school_id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => now()->year,
        ]);
    }
}
