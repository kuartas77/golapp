<?php

declare(strict_types=1);

namespace Tests\Feature;

use Closure;
use Tests\TestCase;
use App\Models\User;
use Tests\WithLogin;
use App\Models\School;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithLogin;

    public function testLoginWrongEmail(): void
    {
        $testResponse = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $testResponse->assertStatus(302);
        $testResponse->assertRedirect('/');
    }

    public function testLoginWrongPassword(): void
    {
        $testResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'passwords'
        ]);

        $testResponse->assertStatus(302);
        $testResponse->assertRedirect('/');
    }

    public function testLoginSchoolSuccess(): void
    {
        Cache::shouldReceive('remember')->once();

        $testResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $testResponse->assertStatus(302);
        $testResponse->assertRedirect('/home');
    }

    public function testLoginInstructorSuccess(): void
    {
        list( , $this->user) = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        Cache::shouldReceive('remember')->once();

        $testResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $testResponse->assertStatus(302);
        $testResponse->assertRedirect('/home');
    }

    public function testLoginSuperAdminSuccess(): void
    {
        list( , $this->user) = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        Cache::shouldReceive('remember')->never();

        $testResponse = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $testResponse->assertStatus(302);
        $testResponse->assertRedirect('/home');
    }

    public function testLogout(): void
    {
        list( , $this->user) = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $this->actingAs($this->user);

        $testResponse = $this->post(route('logout') );

        $testResponse->assertStatus(302);
        $testResponse->assertRedirect('/');
    }

    public function testLogoutJson(): void
    {
        list( , $this->user) = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        $this->actingAs($this->user);

        $testResponse = $this->post(route('logout'), [], ['Content-Type'=>'application/json', 'Accept' => 'application/json'] );

        $testResponse->assertStatus(204);
    }
}
