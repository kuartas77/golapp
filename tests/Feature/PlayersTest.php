<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\ErrorLog;
use App\Models\Player;
use Illuminate\Support\Facades\Mail;
use App\Repositories\PlayerRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegisterPlayerNotification;

class PlayersTest extends TestCase
{

    public function testPlayerValidateForm()
    {
        $this->actingAs($this->user);

        $response = $this->post('/players');
        $response->assertStatus(302);
    }

    public function testPlayerCreate()
    {
        Notification::fake();
        Mail::fake();

        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->actingAs($this->user);

        $response = $this->post('/players', $dataPlayer);

        $player = Player::firstWhere('unique_code', $uniqueCode);

        $spy = $this->spy(PlayerRepository::class);
        $spy->shouldReceive('createPlayer')->andReturn($player);
        Notification::assertSentTo([$player], RegisterPlayerNotification::class);
        Mail::assertNotSent(ErrorLog::class);
        $response->assertStatus(302);
        return $dataPlayer;
    }

    public function testPlayerCreateError()
    {
        Notification::fake();
        Mail::fake();

        $dataPlayer = $this->dataPlayer();
        unset($dataPlayer['people']);

        $this->actingAs($this->user);

        $response = $this->post('/players', $dataPlayer);

        Notification::assertNothingSent();
        Mail::assertSent(ErrorLog::class);
        $response->assertStatus(302);
    }

    public function testPlayerUpdate()
    {
        Mail::fake();

        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $response = $this->post("/players/$uniqueCode", $dataPlayer + ['_method' => 'PATCH']);

        $player = Player::firstWhere('unique_code', $uniqueCode);

        $spy = $this->spy(PlayerRepository::class);
        $spy->shouldReceive('updatePlayer')->andReturn($player);
        Mail::assertNotSent(ErrorLog::class);
        $response->assertStatus(302);
    }

    public function testPlayerUpdateError()
    {
        Mail::fake();

        $dataPlayer = $this->dataPlayer();
        unset($dataPlayer['people']);

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $response = $this->post("/players/$uniqueCode", $dataPlayer + ['_method' => 'PATCH']);

        Mail::assertSent(ErrorLog::class);
        $response->assertStatus(302);
    }

    public function testPlayerIndex()
    {
        $this->actingAs($this->user);

        $response = $this->get("/players");

        $response->assertSeeText('Deportistas');
    }

    public function testPlayerShow()
    {
        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $response = $this->get("/players/$uniqueCode");

        $response->assertSee('Deportista');
    }

    public function testPlayerCreateForm()
    {
        $this->actingAs($this->user);

        $response = $this->get("/players/create");

        $response->assertSee('Agregar Deportista');
    }

    public function testPlayerEditForm()
    {
        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $response = $this->get("/players/$uniqueCode/edit");

        $response->assertSee("Código Deportista: $uniqueCode");
    }

    public function testPlayerDestroy()
    {
        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $response = $this->post("/players/$uniqueCode", ['_method' => 'DELETE']);

        $response->assertStatus(401);
    }
}
