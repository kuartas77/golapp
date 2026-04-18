<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Mail\ErrorLog;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use App\Models\Tournament;
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

    public function testCreateInscriptionWithBrotherPaymentUsesBrotherMonthlyAmount(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()->where('setting_key', Setting::BROTHER_MONTHLY_PAYMENT)->update(['value' => '65000']);
        $player = Player::factory()->create();
        $monthField = config('variables.KEY_INDEX_MONTHS')[$now->month];

        $this->actingAs($this->user);

        $this->post(route('inscriptions.store'), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'brother_payment' => true,
        ])->assertStatus(200);

        $inscription = Inscription::query()->where('player_id', $player->id)->latest('id')->firstOrFail();
        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

        $this->assertTrue((bool) $inscription->brother_payment);
        $this->assertSame(Payment::$debt, (int) $payment->{$monthField});
        $this->assertSame(65000, (int) $payment->{"{$monthField}_amount"});
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

        $testResponse = $this->postJson(route('inscriptions.store'), [
            'unique_code' => 'INVALID-CODE',
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
        ]);

        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['unique_code']);
        Mail::assertNotSent(ErrorLog::class);
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
        $dataInscription['unique_code'] = 'INVALID-CODE';

        $this->actingAs($this->user);

        $testResponse = $this->patchJson(route('inscriptions.update', [$inscription->id]), $dataInscription);
        $testResponse->assertStatus(422);
        $testResponse->assertJsonValidationErrors(['unique_code']);
        Mail::assertNotSent(ErrorLog::class);
        Notification::assertNothingSent();
        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'photos' => false,
        ]);
    }

    public function testUpdateInscriptionCanClearCompetitionGroups(): void
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

        $tournament = Tournament::query()->create([
            'name' => 'Torneo de prueba',
            'school_id' => $this->school['id'],
        ]);

        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Grupo de prueba',
            'year' => (string) $now->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => categoriesName(Carbon::parse($player->date_birth)->year),
            'school_id' => $this->school['id'],
        ]);

        $inscription->competitionGroup()->attach($competitionGroup->id);

        $this->actingAs($this->user);

        $testResponse = $this->post(route('inscriptions.update', [$inscription->id]), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'competition_groups' => [],
            '_method' => 'PATCH',
        ]);

        $testResponse->assertStatus(200);

        $this->assertDatabaseMissing('competition_group_inscription', [
            'competition_group_id' => $competitionGroup->id,
            'inscription_id' => $inscription->id,
        ]);
    }

    public function testUpdateInscriptionRecalculatesDebtMonthsWhenBrotherPaymentChanges(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()->where('setting_key', Setting::MONTHLY_PAYMENT)->update(['value' => '50000']);
        $school->settingsValues()->where('setting_key', Setting::BROTHER_MONTHLY_PAYMENT)->update(['value' => '65000']);

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
            'brother_payment' => false,
        ]);

        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();
        $monthField = config('variables.KEY_INDEX_MONTHS')[$now->month];
        $paidField = collect(config('variables.KEY_INDEX_MONTHS'))
            ->values()
            ->first(fn(string $field) => $field !== $monthField);

        $payment->forceFill([
            $monthField => Payment::$debt,
            "{$monthField}_amount" => 50000,
            $paidField => Payment::$paid,
            "{$paidField}_amount" => 50000,
        ])->save();

        $this->actingAs($this->user);

        $this->post(route('inscriptions.update', [$inscription->id]), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'brother_payment' => true,
            '_method' => 'PATCH',
        ])->assertStatus(200);

        $payment->refresh();

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'brother_payment' => true,
        ]);
        $this->assertSame(65000, (int) $payment->{"{$monthField}_amount"});
        $this->assertSame(50000, (int) $payment->{"{$paidField}_amount"});
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
        $testResponse->assertStatus(200);
        $testResponse->assertJsonPath('success', true);

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
            "player_id",
            "competition_groups",
        ]);
    }

    public function testGetEditById(): void
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

        $testResponse = $this->get(route('inscriptions.edit', [$inscription->id]));

        $testResponse->assertStatus(200);
        $testResponse->assertJson([
            'id' => $inscription->id,
            'player_id' => $player->id,
            'brother_payment' => false,
        ]);
        $testResponse->assertJsonStructure([
            "id",
            "player_id",
            "competition_groups",
            "brother_payment",
        ]);
    }
}
