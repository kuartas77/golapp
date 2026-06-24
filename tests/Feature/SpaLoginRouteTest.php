<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaLoginRouteTest extends TestCase
{
    public function test_legacy_login_url_redirects_to_the_spa_login(): void
    {
        $this->get('/login')
            ->assertRedirect('/ingreso');

        $this->assertSame(url('/login'), route('login'));
    }
}
