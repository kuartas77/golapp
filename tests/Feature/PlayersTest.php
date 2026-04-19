<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\ErrorLog;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Repositories\PlayerRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegisterPlayerNotification;
use Illuminate\Testing\TestResponse;

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
        $this->createUser([
            'email' => sprintf('player-errors-%s@example.com', uniqid()),
        ], ['super-admin']);

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

        $dataPlayer = $this->playerUpdatePayload();
        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $testResponse = $this->withHeaders($this->apiHeaders())
            ->putJson("/api/v2/players/{$uniqueCode}", $dataPlayer);

        $player = Player::firstWhere('unique_code', $uniqueCode);

        Mail::assertNotSent(ErrorLog::class);
        $testResponse->assertStatus(200);
        $testResponse->assertJsonPath('success', true);
        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'names' => $dataPlayer['names'],
            'mobile' => $dataPlayer['phones'],
        ]);
    }

    public function testPlayerUpdateError(): void
    {
        Mail::fake();

        $dataPlayer = $this->playerUpdatePayload([
            'names' => '',
        ]);

        $uniqueCode = $dataPlayer['unique_code'];

        $this->createPlayer();

        $testResponse = $this->withHeaders($this->apiHeaders())
            ->putJson("/api/v2/players/{$uniqueCode}", $dataPlayer);

        Mail::assertNotSent(ErrorLog::class);
        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['names']);
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

        $testResponse = $this->withHeaders($this->apiHeaders())
            ->getJson("/api/v2/players/{$uniqueCode}");

        $testResponse->assertStatus(200);
        $testResponse->assertJsonPath('unique_code', $uniqueCode);
        $testResponse->assertJsonPath('names', $dataPlayer['names']);
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

        $testResponse = $this->withHeaders($this->apiHeaders())
            ->getJson("/api/v2/players/{$uniqueCode}/edit");

        $testResponse->assertStatus(200);
        $testResponse->assertJsonPath('unique_code', $uniqueCode);
        $testResponse->assertJsonPath('identification_document', $dataPlayer['identification_document']);
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

    private function apiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->loginByApi($this->user)->json('access_token'),
            'Accept' => 'application/json',
        ];
    }

    private function loginByApi(User $user): TestResponse
    {
        return $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();
    }

    private function playerUpdatePayload(array $overrides = []): array
    {
        $dataPlayer = $this->dataPlayer();
        $people = $dataPlayer['people'];
        unset($dataPlayer['people']);

        $payload = $dataPlayer + [
            'relationship_0' => $people[0]['relationship'],
            'names_0' => $people[0]['names'],
            'document_0' => $people[0]['identification_card'],
            'phone_0' => $people[0]['phone'],
            'business_0' => 'Negocio Tutor',
            'relationship_1' => 'null',
            'names_1' => 'null',
            'document_1' => 'null',
            'phone_1' => 'null',
            'business_1' => 'null',
            'relationship_2' => 'null',
            'names_2' => 'null',
            'document_2' => 'null',
            'phone_2' => 'null',
            'business_2' => 'null',
        ];

        return array_merge($payload, $overrides);
    }
}
