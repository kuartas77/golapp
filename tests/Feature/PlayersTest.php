<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\ErrorLog;
use App\Models\Player;
use Illuminate\Support\Facades\Mail;
use App\Repositories\PlayerRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegisterPlayerNotification;

final class PlayersTest extends TestCase
{

    public function testPlayerValidateForm(): void
    {
        $this->actingAs($this->user);

        $testResponse = $this->post('/players');
        $testResponse->assertStatus(302);
    }

    /**
     * @return mixed[]
     */
    public function testPlayerCreate(): array
    {
        Notification::fake();
        Mail::fake();

        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->actingAs($this->user);

        $testResponse = $this->post('/players', $dataPlayer);

        $player = Player::firstWhere('unique_code', $uniqueCode);

        $testResponse->assertStatus(302);
        $spy = $this->spy(PlayerRepository::class);
        $spy->shouldReceive('createPlayer')->andReturn($player);
        Notification::assertSentTo([$player], RegisterPlayerNotification::class);
        Mail::assertNotSent(ErrorLog::class);
        return $dataPlayer;
    }

    public function testPlayerCreateError(): void
    {
        Notification::fake();
        Mail::fake();

        $dataPlayer = $this->dataPlayer();
        unset($dataPlayer['people']);

        $this->actingAs($this->user);

        $testResponse = $this->post('/players', $dataPlayer);

        Notification::assertNothingSent();
        Mail::assertSent(ErrorLog::class);
        $testResponse->assertStatus(302);
    }

    public function testPlayerUpdate(): void
    {
        Mail::fake();

        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $testResponse = $this->post('/players/' . $uniqueCode, $dataPlayer + ['_method' => 'PATCH']);

        $player = Player::firstWhere('unique_code', $uniqueCode);

        $spy = $this->spy(PlayerRepository::class);
        $spy->shouldReceive('updatePlayer')->andReturn($player);
        Mail::assertNotSent(ErrorLog::class);
        $testResponse->assertStatus(302);
    }

    public function testPlayerUpdateError(): void
    {
        Mail::fake();

        $dataPlayer = $this->dataPlayer();
        unset($dataPlayer['people']);

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $testResponse = $this->post('/players/' . $uniqueCode, $dataPlayer + ['_method' => 'PATCH']);

        Mail::assertSent(ErrorLog::class);
        $testResponse->assertStatus(302);
    }

    public function testPlayerIndex(): void
    {
        $this->actingAs($this->user);

        $testResponse = $this->get("/players");

        $testResponse->assertSeeText('Deportistas');
    }

    public function testPlayerShow(): void
    {
        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $testResponse = $this->get('/players/' . $uniqueCode);

        $testResponse->assertSee('Deportista');
    }

    public function testPlayerCreateForm(): void
    {
        $this->actingAs($this->user);

        $testResponse = $this->get("/players/create");

        $testResponse->assertSee('Agregar Deportista');
    }

    public function testPlayerEditForm(): void
    {
        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $testResponse = $this->get(sprintf('/players/%s/edit', $uniqueCode));

        $testResponse->assertSee('Código Deportista: ' . $uniqueCode);
    }

    public function testPlayerDestroy(): void
    {
        $dataPlayer = $this->dataPlayer();

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $this->actingAs($this->user);

        $testResponse = $this->post('/players/' . $uniqueCode, ['_method' => 'DELETE']);

        $testResponse->assertStatus(401);
    }
}
