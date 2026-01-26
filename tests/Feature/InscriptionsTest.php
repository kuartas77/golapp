<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Mail\ErrorLog;
use App\Models\Inscription;
use App\Models\Player;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Repositories\InscriptionRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\InscriptionNotification;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class InscriptionsTest extends TestCase
{

    public function testPlayerValidateForm(): void
    {
        $this->actingAs($this->user);
        $testResponse = $this->post(route('inscriptions.store'));
        $testResponse->assertStatus(302);
    }

    public function testCreateInscription(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $player = Player::factory()->create();

        $dataInscription = [];
        $dataInscription['unique_code'] = $player->unique_code;
        $dataInscription['player_id'] = $player->id;
        $dataInscription['start_date'] = $now->format('Y-m-d');

        $this->actingAs($this->user);

        $testResponse = $this->post(route('inscriptions.store'), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
        ]);

        $testResponse->assertStatus(200);
        Mail::assertNotSent(ErrorLog::class);
        Notification::assertSentTo($player, InscriptionNotification::class);
        $this->assertDatabaseHas('inscriptions', ['player_id' => $player->id]);
    }

    public function testCreateInscriptionError(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $player = Player::factory()->create();

        $dataInscription = [];
        $dataInscription['unique_code'] = $player->unique_code;
        $dataInscription['player_id'] = $player->id;
        $dataInscription['start_date'] = $now->format('Y-m-d');

        $this->actingAs($this->user);

        $testResponse = $this->post(route('inscriptions.store'), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'competition_groups' => [1, 2, 3, 4, 5]
        ]);

        $testResponse->assertStatus(422);
        Mail::assertSent(ErrorLog::class);
        Notification::assertNotSentTo($player, InscriptionNotification::class);
        $this->assertDatabaseEmpty('inscriptions');
    }

    public function testUptadeInscription(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();

        $player = Player::factory()->create();
        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $now->year,
            'training_group_id' => 1,
            'competition_group_id' => null,
            'start_date' => $now->format('Y-m-d'),
            'category' => categoriesName(Carbon::parse($player->date_birth)->year),
            'school_id' => $this->school['id']
        ]);

        $dataInscription = [];
        $dataInscription['unique_code'] = $player->unique_code;
        $dataInscription['player_id'] = $player->id;
        $dataInscription['start_date'] = $now->format('Y-m-d');
        $dataInscription['photos'] = true;

        $this->actingAs($this->user);

        $testResponse = $this->post(route('inscriptions.update', [$inscription->id]), $dataInscription + ['_method' => 'PATCH']);
        $testResponse->assertStatus(200);

        $this->assertDatabaseHas('inscriptions', ['player_id' => $player->id, 'photos' => true]);

        Notification::assertNotSentTo($player, InscriptionNotification::class);
        Mail::assertNotSent(ErrorLog::class);
    }

    public function testUptadeInscriptionError(): void
    {
        Mail::fake();

        $now = Carbon::now();

        $player = Player::factory()->create();
        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $now->year,
            'training_group_id' => 1,
            'competition_group_id' => null,
            'start_date' => $now->format('Y-m-d'),
            'category' => categoriesName(Carbon::parse($player->date_birth)->year),
            'school_id' => $this->school['id']
        ]);

        $dataInscription = [];
        $dataInscription['unique_code'] = $player->unique_code;
        $dataInscription['player_id'] = $player->id;
        $dataInscription['start_date'] = $now->format('Y-m-d');
        $dataInscription['photos'] = true;
        $dataInscription['competition_groups'] = [1, 2, 3, 4, 5];

        $this->actingAs($this->user);

        $testResponse = $this->post(route('inscriptions.update', [$inscription->id]), $dataInscription + ['_method' => 'PATCH']);
        $testResponse->assertStatus(422);
        Mail::assertSent(ErrorLog::class);
    }

    public function testGetIndex(): void
    {
        $this->actingAs($this->user);
        $testResponse = $this->get(route('inscriptions.index'));
        $testResponse->assertStatus(200);
        $testResponse->assertSee('Inscripciones');
    }

    public function testDeleteInscription(): void
    {
        Mail::fake();

        $now = Carbon::now();

        $player = Player::factory()->create();
        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $now->year,
            'training_group_id' => 1,
            'competition_group_id' => null,
            'start_date' => $now->format('Y-m-d'),
            'category' => categoriesName(Carbon::parse($player->date_birth)->year),
            'school_id' => $this->school['id']
        ]);

        $this->actingAs($this->user);

        $testResponse = $this->post(route('inscriptions.destroy', [$inscription->id]), ['_method' => 'DELETE']);
        $testResponse->assertStatus(302);

        $this->assertDatabaseHas('inscriptions', ['id' => $inscription->id, 'deleted_at' => $now]);
    }

    public function testGetEdit(): void
    {
        $now = Carbon::now();

        $player = Player::factory()->create();
        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $now->year,
            'training_group_id' => 1,
            'competition_group_id' => null,
            'start_date' => $now->format('Y-m-d'),
            'category' => categoriesName(Carbon::parse($player->date_birth)->year),
            'school_id' => $this->school['id']
        ]);

        $this->actingAs($this->user);

        $testResponse = $this->get(route('inscriptions.edit', [$inscription->unique_code]));

        $testResponse->assertStatus(200);

        $testResponse->assertJsonStructure([
            "id",
            "player_id"
        ]);
    }
}
