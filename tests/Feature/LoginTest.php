<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\WithLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithLogin;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_login_success()
    {
        list($_, $user) = $this->createSchoolAndUser();

        $this->post('/login', [
            'email' => $user['email'],
            'password' => 'password'
        ])
        ->assertStatus(302)
        ->assertRedirect('/home');
    }

    public function test_login_email_password_wrong()
    {
        list($_, $user) = $this->createSchoolAndUser();

        $this->post('/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ])
        ->assertStatus(302)
        ->assertRedirect('/');

        $this->post('/login', [
            'email' => $user['email'],
            'password' => 'passwords'
        ])
        ->assertStatus(302)
        ->assertRedirect('/');
    }
}
