<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\WithLogin;

final class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithLogin;

    public function test_login_wrong_email(): void
    {
        $testResponse = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['email']);
    }

    public function test_login_wrong_password(): void
    {
        $testResponse = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'passwords',
        ]);

        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['email']);
    }

    public function test_login_school_success(): void
    {
        $testResponse = $this->loginByApi($this->user);

        $testResponse->assertOk();
        $testResponse->assertJsonPath('token_type', 'Bearer');
        $testResponse->assertJsonPath('user.email', $this->user->email);
        $this->assertContains('school', $testResponse->json('user.roles', []));
    }

    public function test_login_instructor_success(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        $testResponse = $this->loginByApi($this->user);

        $testResponse->assertOk();
        $testResponse->assertJsonPath('token_type', 'Bearer');
        $testResponse->assertJsonPath('user.email', $this->user->email);
        $this->assertContains('instructor', $testResponse->json('user.roles', []));
    }

    public function test_login_super_admin_success(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $testResponse = $this->loginByApi($this->user);

        $testResponse->assertOk();
        $testResponse->assertJsonPath('token_type', 'Bearer');
        $testResponse->assertJsonPath('user.email', $this->user->email);
        $this->assertContains('super-admin', $testResponse->json('user.roles', []));
    }

    public function test_logout(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $accessToken = $this->loginByApi($this->user)->json('access_token');

        $testResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/logout');

        $testResponse->assertOk();
        $testResponse->assertJson(['success' => true]);
    }

    public function test_logout_json(): void
    {
        [, $this->user] = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $accessToken = $this->loginByApi($this->user)->json('access_token');

        $testResponse = $this->withHeader('Authorization', "Bearer {$accessToken}")
            ->postJson('/api/logout');

        $testResponse->assertOk();
        $testResponse->assertJson(['success' => true]);
    }

    public function test_legacy_player_login_is_retired(): void
    {
        $this->postJson('/api/notify/login', [
            'email' => 'player@example.com',
            'password' => '1002003004',
        ])->assertStatus(410)
            ->assertJsonPath('message', 'Este acceso fue retirado. Ingresa mediante el Portal de Acudientes.');
    }

    private function loginByApi(User $user): TestResponse
    {
        return $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
