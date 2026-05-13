<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Mail\ErrorLog;
use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\Invoice;
use App\Models\InvoiceCustomItem;
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

    public function testCreateInscriptionWithCustomChargeSnapshot(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $player = Player::factory()->create();
        $customItem = InvoiceCustomItem::query()->create([
            'type' => 'OTHER',
            'name' => 'Seguro anual',
            'unit_price' => 75000,
            'school_id' => $this->school['id'],
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('inscriptions.store'), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'custom_charges' => [
                [
                    'invoice_custom_item_id' => $customItem->id,
                    'value' => '$ 80.000',
                    'due_date' => $now->copy()->addDay()->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertStatus(200);

        $inscription = Inscription::query()->firstWhere('player_id', $player->id);

        $this->assertDatabaseHas('inscription_custom_charges', [
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'invoice_custom_item_id' => $customItem->id,
            'name' => 'Seguro anual',
            'value' => 80000,
            'status' => InscriptionCustomCharge::STATUS_PENDING,
        ]);
    }

    public function testCreateInvoiceWithDueCustomChargeMarksItPaidWhenItemIsPaid(): void
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
            'school_id' => $this->school['id'],
        ]);
        $customItem = InvoiceCustomItem::query()->create([
            'type' => 'OTHER',
            'name' => 'Seguro anual',
            'unit_price' => 75000,
            'school_id' => $this->school['id'],
        ]);
        $charge = InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'invoice_custom_item_id' => $customItem->id,
            'name' => 'Seguro anual',
            'value' => 75000,
            'status' => InscriptionCustomCharge::STATUS_DUE,
            'due_date' => $now->format('Y-m-d'),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('invoices.store'), [
            'inscription_id' => $inscription->id,
            'training_group_id' => $inscription->training_group_id,
            'year' => $now->year,
            'student_name' => $player->full_names,
            'due_date' => $now->copy()->addDays(15)->format('Y-m-d'),
            'notes' => 'Test',
            'items' => [
                [
                    'type' => 'additional',
                    'description' => $charge->name,
                    'quantity' => 1,
                    'unit_price' => 75000,
                    'custom_charge_id' => $charge->id,
                ],
            ],
        ]);

        $response->assertStatus(302);

        $charge->refresh();
        $this->assertNotNull($charge->invoice_item_id);
        $this->assertSame(InscriptionCustomCharge::STATUS_DUE, $charge->status);

        $invoice = Invoice::query()->latest('id')->first();
        $item = $invoice->items()->first();

        $this->post(route('invoices.addPayment', [$invoice->id]), [
            'amount' => 75000,
            'payment_method' => 'cash',
            'issue_date' => $now->format('Y-m-d'),
            'payment_date' => $now->format('Y-m-d'),
            'paid_items' => [$item->id],
        ])->assertStatus(302);

        $this->assertDatabaseHas('inscription_custom_charges', [
            'id' => $charge->id,
            'status' => InscriptionCustomCharge::STATUS_PAID,
        ]);
    }

    public function testMarkCustomChargesDueCommandOnlyUpdatesExpiredPendingCharges(): void
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
            'school_id' => $this->school['id'],
        ]);

        $expiredCharge = InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'name' => 'Vencido',
            'value' => 10000,
            'status' => InscriptionCustomCharge::STATUS_PENDING,
            'due_date' => $now->copy()->subDay()->format('Y-m-d'),
        ]);
        $futureCharge = InscriptionCustomCharge::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'player_id' => $player->id,
            'name' => 'Futuro',
            'value' => 10000,
            'status' => InscriptionCustomCharge::STATUS_PENDING,
            'due_date' => $now->copy()->addDay()->format('Y-m-d'),
        ]);

        $this->artisan('charges:mark-due')->assertSuccessful();

        $this->assertSame(InscriptionCustomCharge::STATUS_DUE, $expiredCharge->fresh()->status);
        $this->assertSame(InscriptionCustomCharge::STATUS_PENDING, $futureCharge->fresh()->status);
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

        $this->assertSoftDeleted('inscriptions', ['id' => $inscription->id]);
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
