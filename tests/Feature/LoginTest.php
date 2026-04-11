<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Tests\WithLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

final class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithLogin;

    public function testLoginWrongEmail(): void
    {
        $testResponse = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['email']);
    }

    public function testLoginWrongPassword(): void
    {
        $testResponse = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'passwords'
        ]);

        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['email']);
    }

    public function testLoginSchoolSuccess(): void
    {
        $testResponse = $this->loginByApi($this->user);

        $testResponse->assertOk();
        $testResponse->assertJsonPath('token_type', 'Bearer');
        $testResponse->assertJsonPath('user.email', $this->user->email);
        $this->assertContains('school', $testResponse->json('user.roles', []));
    }

    public function testLoginInstructorSuccess(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        $testResponse = $this->loginByApi($this->user);

        $testResponse->assertOk();
        $testResponse->assertJsonPath('token_type', 'Bearer');
        $testResponse->assertJsonPath('user.email', $this->user->email);
        $this->assertContains('instructor', $testResponse->json('user.roles', []));
    }

    public function testLoginSuperAdminSuccess(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $testResponse = $this->loginByApi($this->user);

        $testResponse->assertOk();
        $testResponse->assertJsonPath('token_type', 'Bearer');
        $testResponse->assertJsonPath('user.email', $this->user->email);
        $this->assertContains('super-admin', $testResponse->json('user.roles', []));
    }

    public function testLogout(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $accessToken = $this->loginByApi($this->user)->json('access_token');

        $testResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/logout');

        $testResponse->assertOk();
        $testResponse->assertJson(['success' => true]);
    }

    public function testLogoutJson(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $accessToken = $this->loginByApi($this->user)->json('access_token');

        $testResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/logout');

        $testResponse->assertOk();
        $testResponse->assertJson(['success' => true]);
    }

    private function loginByApi(User $user): TestResponse
    {
        return $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
    }
}
