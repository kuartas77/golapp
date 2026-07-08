<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class SpaLoginPasswordRehashTest extends TestCase
{
    public function testPasswordRehashDuringLoginKeepsCredentialsValid(): void
    {
        config()->set('hashing.bcrypt.rounds', 5);
        Hash::driver()->setRounds(5);

        $email = 'rehash-login@gmail.com';

        DB::table('users')->where('id', $this->user->id)->update([
            'email' => $email,
            'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 4]),
        ]);

        $this->postJson('/api/v2/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertOk();

        Auth::guard('web')->logout();

        $this->postJson('/api/v2/login', [
            'email' => $email,
            'password' => 'password',
        ])->assertOk();

        $password = (string) $this->user->fresh()->password;

        $this->assertTrue(Hash::check('password', $password));
        $this->assertFalse(Hash::needsRehash($password));
    }
}
