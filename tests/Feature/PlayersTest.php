<?php

namespace Tests\Feature;

use App\Models\Player;
use Tests\TestCase;
use Tests\WithLogin;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegisterPlayerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayersTest extends TestCase
{
    use RefreshDatabase;
    use WithLogin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    }

    public function test_validate_form()
    {
        list($_, $user) = $this->createSchoolAndUser();

        $this->actingAs($user);

        $response = $this->post('/players');
        $response->assertStatus(302);
    }

    public function test_create_player()
    {
        Notification::fake();

        list($_, $user) = $this->createSchoolAndUser();

        $this->actingAs($user);

        $response = $this->post('/players', [
            'unique_code' => '1111111111',
            'names' => 'juan esteban',
            'last_names' => 'cuartas londoño',
            'gender' => 'M',
            'date_birth' => '1989-02-13',
            'place_birth' => 'Medellin',
            'identification_document' => '1017170333',
            'rh' => 'O+',
            'eps' => 'Sura',
            'email' => 'teste@gmail.com',
            'address' => 'calle falsa 123',
            'municipality' => 'Medellin',
            'neighborhood' => 'Robledo, Pilarica',
            'zone' => '',
            'commune' => '7',
            'phones' => '111222222',
            'mobile' => '113333333',
            'school' => 'Pascual',
            'degree' => '11',
            'people' => [
                [
                    "tutor" => "true",
                "relationship" => "30",
                "names" => "CRISTINA VANEGAS",
                "identification_card" => "3015614556",
                "phone" => "5961994"
                ]
            ]
        ]);

        $player = Player::firstWhere('unique_code', '1111111111');

        $response->assertStatus(302);

        Notification::assertSentTo([$player], RegisterPlayerNotification::class);
    }
}
