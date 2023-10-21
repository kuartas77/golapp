<?php

namespace Tests\Feature;

use Closure;
use Tests\TestCase;
use App\Models\User;
use Tests\WithLogin;
use App\Models\School;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithLogin;

    public function testLoginWrongEmail()
    {
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function testLoginWrongPassword()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'passwords'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    public function testLoginSchoolSuccess()
    {
        Cache::shouldReceive('remember')->once();

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }

    public function testLoginInstructorSuccess()
    {
        list( , $this->user) = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        Cache::shouldReceive('remember')->once();

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }

    public function testLoginSuperAdminSuccess()
    {
        list( , $this->user) = $this->createSchoolAndUser(roles: [User::SUPER_ADMIN]);

        Cache::shouldReceive('remember')->never();

        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }
}
