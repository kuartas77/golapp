<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Notifications\UserPasswordResetNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class AuthPasswordRecoveryTest extends TestCase
{
    public function testUserCanRequestPasswordRecoveryInstructions(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v2/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Si existe una cuenta válida, recibirás instrucciones en tu correo.');

        Notification::assertSentTo($this->user, UserPasswordResetNotification::class);
    }

    public function testUserCanResetPasswordFromRecoveryToken(): void
    {
        $token = Password::broker('users')->createToken($this->user);

        $response = $this->postJson('/api/v2/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'La contraseña fue actualizada correctamente.');
        $this->assertTrue(Hash::check('NuevaClave123', (string) $this->user->fresh()->password));
    }
}
