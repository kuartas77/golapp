<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ErrorLog;
use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Notifications\InscriptionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class InscriptionsTest extends TestCase
{
    public function test_player_validate_form(): void
    {
        $this->actingAs($this->user);
        $testResponse = $this->post(route('inscriptions.store'));
        $testResponse->assertStatus(302);
    }

    public function test_create_inscription(): void
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
        $this->assertDatabaseHas('inscriptions', [
            'player_id' => $player->id,
            'monthly_payment_type' => Setting::MONTHLY_PAYMENT,
            'monthly_payment_amount' => 50000,
        ]);
    }

    public function test_create_inscription_sets_zero_amounts_for_non_applicable_months(): void
    {
        Mail::fake();
        Notification::fake();

        $player = Player::factory()->create();

        $this->actingAs($this->user);

        $this->post(route('inscriptions.store'), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => '2026-03-15',
        ])->assertStatus(200);

        $inscription = Inscription::query()->where('player_id', $player->id)->latest('id')->firstOrFail();
        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

        $this->assertSame(Payment::$no_application, (int) $payment->january);
        $this->assertSame(0, (int) $payment->january_amount);
        $this->assertSame(Payment::$no_application, (int) $payment->february);
        $this->assertSame(0, (int) $payment->february_amount);
        $this->assertSame(Payment::$debt, (int) $payment->march);
        $this->assertSame(50000, (int) $payment->march_amount);
    }

    public function test_inscription_email_shows_school_name_to_guardian(): void
    {
        $template = file_get_contents(resource_path('views/emails/inscriptions/added.blade.php'));

        $this->assertStringContainsString('* Escuela: {{ $inscription->school->name }}', $template);
    }

    public function test_inscription_limit_summary_reports_current_and_remaining_slots(): void
    {
        $year = now()->year;
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->update(['value' => '3']);

        Player::factory()->count(2)->create()->each(function (Player $player) use ($year, $school): void {
            Inscription::factory()->create([
                'player_id' => $player->id,
                'unique_code' => $player->unique_code,
                'year' => $year,
                'school_id' => $school->id,
                'competition_group_id' => null,
            ]);
        });

        $this->actingAs($this->user)
            ->getJson("/api/v2/inscriptions/limit-summary?year={$year}")
            ->assertOk()
            ->assertJson([
                'year' => $year,
                'current' => 2,
                'limit' => 3,
                'remaining' => 1,
                'is_full' => false,
            ]);
    }

    public function test_create_inscription_is_blocked_when_school_reaches_year_limit(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()
            ->where('setting_key', Setting::MAX_INSCRIPTIONS)
            ->update(['value' => '1']);

        $existingPlayer = Player::factory()->create();
        Inscription::factory()->create([
            'player_id' => $existingPlayer->id,
            'unique_code' => $existingPlayer->unique_code,
            'year' => $now->year,
            'school_id' => $school->id,
            'competition_group_id' => null,
        ]);

        $player = Player::factory()->create();

        $this->actingAs($this->user)
            ->postJson(route('inscriptions.store'), [
                'unique_code' => $player->unique_code,
                'player_id' => $player->id,
                'start_date' => $now->format('Y-m-d'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['max_inscriptions']);

        $this->assertDatabaseMissing('inscriptions', [
            'player_id' => $player->id,
            'school_id' => $school->id,
            'year' => $now->year,
        ]);
    }

    public function test_create_inscription_with_brother_payment_uses_brother_monthly_amount(): void
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
            'monthly_payment_type' => Setting::BROTHER_MONTHLY_PAYMENT,
        ])->assertStatus(200);

        $inscription = Inscription::query()->where('player_id', $player->id)->latest('id')->firstOrFail();
        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

        $this->assertTrue((bool) $inscription->brother_payment);
        $this->assertSame(Setting::BROTHER_MONTHLY_PAYMENT, $inscription->monthly_payment_type);
        $this->assertSame(65000, (int) $inscription->monthly_payment_amount);
        $this->assertSame(Payment::$debt, (int) $payment->{$monthField});
        $this->assertSame(65000, (int) $payment->{"{$monthField}_amount"});
    }

    public function test_create_inscription_with_legacy_brother_payment_payload_uses_brother_monthly_amount(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()->where('setting_key', Setting::BROTHER_MONTHLY_PAYMENT)->update(['value' => '64000']);
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
        $this->assertSame(Setting::BROTHER_MONTHLY_PAYMENT, $inscription->monthly_payment_type);
        $this->assertSame(64000, (int) $inscription->monthly_payment_amount);
        $this->assertSame(64000, (int) $payment->{"{$monthField}_amount"});
    }

    public function test_create_inscription_with_extra_monthly_payment_options_uses_selected_snapshot(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $monthField = config('variables.KEY_INDEX_MONTHS')[$now->month];
        $types = [
            Setting::MONTHLY_PAYMENT_OPTION_1 => 55000,
            Setting::MONTHLY_PAYMENT_OPTION_2 => 60000,
            Setting::MONTHLY_PAYMENT_OPTION_3 => 70000,
        ];

        foreach ($types as $type => $amount) {
            $school->settingsValues()->where('setting_key', $type)->update(['value' => (string) $amount]);
        }

        $this->actingAs($this->user);

        foreach ($types as $type => $amount) {
            $player = Player::factory()->create();

            $this->post(route('inscriptions.store'), [
                'unique_code' => $player->unique_code,
                'player_id' => $player->id,
                'start_date' => $now->format('Y-m-d'),
                'monthly_payment_type' => $type,
            ])->assertStatus(200);

            $inscription = Inscription::query()->where('player_id', $player->id)->latest('id')->firstOrFail();
            $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

            $this->assertFalse((bool) $inscription->brother_payment);
            $this->assertSame($type, $inscription->monthly_payment_type);
            $this->assertSame($amount, (int) $inscription->monthly_payment_amount);
            $this->assertSame($amount, (int) $payment->{"{$monthField}_amount"});
        }
    }

    public function test_create_inscription_allows_manual_pre_inscription_outside_provisional_group(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $player = Player::factory()->create();
        $trainingGroup = TrainingGroup::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Grupo definitivo',
            'year' => $now->year,
            'category' => 'Todas las categorías',
            'days' => 'Lunes',
            'schedules' => '10:00AM - 11:00AM',
        ]);

        $this->actingAs($this->user);

        $this->post(route('inscriptions.store'), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'training_group_id' => $trainingGroup->id,
            'pre_inscription' => true,
        ])->assertStatus(200);

        $this->assertDatabaseHas('inscriptions', [
            'player_id' => $player->id,
            'training_group_id' => $trainingGroup->id,
            'pre_inscription' => true,
        ]);
    }

    public function test_create_inscription_error(): void
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

    public function test_uptade_inscription(): void
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

    public function test_uptade_inscription_error(): void
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

    public function test_update_inscription_can_clear_competition_groups(): void
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

    public function test_update_inscription_allows_manual_pre_inscription_outside_provisional_group(): void
    {
        $now = Carbon::now();

        $player = Player::factory()->create();
        $trainingGroup = TrainingGroup::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Grupo definitivo',
            'year' => $now->year,
            'category' => 'Todas las categorías',
            'days' => 'Lunes',
            'schedules' => '10:00AM - 11:00AM',
        ]);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $now->year,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'start_date' => $now->format('Y-m-d'),
            'category' => categoriesName(Carbon::parse($player->date_birth)->year),
            'school_id' => $this->school['id'],
            'pre_inscription' => false,
        ]);

        $this->actingAs($this->user);

        $this->post(route('inscriptions.update', [$inscription->id]), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'training_group_id' => $trainingGroup->id,
            'pre_inscription' => true,
            '_method' => 'PATCH',
        ])->assertStatus(200);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'training_group_id' => $trainingGroup->id,
            'pre_inscription' => true,
        ]);
    }

    public function test_update_inscription_recalculates_debt_months_when_monthly_payment_type_changes(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()->where('setting_key', Setting::MONTHLY_PAYMENT)->update(['value' => '50000']);
        $school->settingsValues()->where('setting_key', Setting::MONTHLY_PAYMENT_OPTION_1)->update(['value' => '62000']);

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
            'monthly_payment_type' => Setting::MONTHLY_PAYMENT,
            'monthly_payment_amount' => 50000,
        ]);

        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();
        $monthField = config('variables.KEY_INDEX_MONTHS')[$now->month];
        $paidField = collect(config('variables.KEY_INDEX_MONTHS'))
            ->values()
            ->first(fn (string $field) => $field !== $monthField);

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
            'monthly_payment_type' => Setting::MONTHLY_PAYMENT_OPTION_1,
            '_method' => 'PATCH',
        ])->assertStatus(200);

        $payment->refresh();

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'brother_payment' => false,
            'monthly_payment_type' => Setting::MONTHLY_PAYMENT_OPTION_1,
            'monthly_payment_amount' => 62000,
        ]);
        $this->assertSame(62000, (int) $payment->{"{$monthField}_amount"});
        $this->assertSame(50000, (int) $payment->{"{$paidField}_amount"});
    }

    public function test_update_inscription_from_brother_to_extra_option_clears_brother_payment(): void
    {
        Mail::fake();
        Notification::fake();

        $now = Carbon::now();
        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()->where('setting_key', Setting::BROTHER_MONTHLY_PAYMENT)->update(['value' => '65000']);
        $school->settingsValues()->where('setting_key', Setting::MONTHLY_PAYMENT_OPTION_2)->update(['value' => '72000']);

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
            'brother_payment' => true,
            'monthly_payment_type' => Setting::BROTHER_MONTHLY_PAYMENT,
            'monthly_payment_amount' => 65000,
        ]);

        $this->actingAs($this->user);

        $this->post(route('inscriptions.update', [$inscription->id]), [
            'unique_code' => $player->unique_code,
            'player_id' => $player->id,
            'start_date' => $now->format('Y-m-d'),
            'monthly_payment_type' => Setting::MONTHLY_PAYMENT_OPTION_2,
            '_method' => 'PATCH',
        ])->assertStatus(200);

        $inscription->refresh();

        $this->assertFalse((bool) $inscription->brother_payment);
        $this->assertSame(Setting::MONTHLY_PAYMENT_OPTION_2, $inscription->monthly_payment_type);
        $this->assertSame(72000, (int) $inscription->monthly_payment_amount);
    }

    public function test_delete_inscription(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-03-15 10:00:00');

        try {
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

            $payment = Payment::query()->where('inscription_id', $inscription->id)->where('year', $now->year)->firstOrFail();
            $assist = Assist::query()->where('inscription_id', $inscription->id)->where('year', $now->year)->where('month', $now->month)->firstOrFail();
            $currentField = config('variables.KEY_INDEX_MONTHS')[$now->month];
            $futureField = config('variables.KEY_INDEX_MONTHS')[$now->month + 1];

            $this->actingAs($this->user);

            $testResponse = $this->post(route('inscriptions.destroy', [$inscription->id]), ['_method' => 'DELETE']);
            $testResponse->assertStatus(200);
            $testResponse->assertJsonPath('success', true);

            $payment->refresh();
            $assist->refresh();

            $this->assertSoftDeleted('inscriptions', ['id' => $inscription->id]);
            $this->assertNull($payment->deleted_at);
            $this->assertNull($assist->deleted_at);
            $this->assertSame(Payment::$debt, (int) $payment->{$currentField});
            $this->assertSame(Payment::$permanent_retirement, (int) $payment->{$futureField});
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_create_inscription_reactivates_soft_deleted_record_and_restores_legacy_relations(): void
    {
        Mail::fake();
        Notification::fake();
        Carbon::setTestNow('2026-03-15 10:00:00');

        try {
            $now = Carbon::now();
            $player = Player::factory()->create();
            $reactivatedGroup = TrainingGroup::query()->create([
                'school_id' => $this->school['id'],
                'name' => 'Grupo reactivado',
                'year' => $now->year,
                'category' => 'Todas las categorías',
                'days' => 'Martes',
                'schedules' => '10:00AM - 11:00AM',
            ]);

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

            $payment = Payment::query()->where('inscription_id', $inscription->id)->where('year', $now->year)->firstOrFail();
            $assist = Assist::query()->where('inscription_id', $inscription->id)->where('year', $now->year)->where('month', $now->month)->firstOrFail();

            $payment->delete();
            $assist->delete();
            $inscription->delete();

            $this->actingAs($this->user);

            $response = $this->postJson(route('inscriptions.store'), [
                'unique_code' => $player->unique_code,
                'player_id' => $player->id,
                'start_date' => $now->copy()->addMonths(2)->format('Y-m-d'),
                'training_group_id' => $reactivatedGroup->id,
                'brother_payment' => true,
                'photos' => true,
            ]);

            $response->assertOk();
            $response->assertJsonPath('success', true);
            $response->assertJsonPath('reactivated', true);

            $reactivatedInscription = Inscription::withTrashed()->findOrFail($inscription->id);
            $restoredPayment = Payment::withTrashed()->where('inscription_id', $inscription->id)->where('year', $now->year)->firstOrFail();
            $restoredAssist = Assist::withTrashed()->where('inscription_id', $inscription->id)->where('year', $now->year)->where('month', $now->month)->firstOrFail();

            $this->assertSame(1, Inscription::withTrashed()->where('player_id', $player->id)->where('year', $now->year)->count());
            $this->assertNull($reactivatedInscription->deleted_at);
            $this->assertSame($now->format('Y-m-d'), Carbon::parse($reactivatedInscription->start_date)->format('Y-m-d'));
            $this->assertSame($reactivatedGroup->id, (int) $reactivatedInscription->training_group_id);
            $this->assertTrue((bool) $reactivatedInscription->brother_payment);
            $this->assertTrue((bool) $reactivatedInscription->photos);
            $this->assertNull($restoredPayment->deleted_at);
            $this->assertSame($reactivatedGroup->id, (int) $restoredPayment->training_group_id);
            $this->assertNull($restoredAssist->deleted_at);
            $this->assertSame($reactivatedGroup->id, (int) $restoredAssist->training_group_id);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_create_inscription_reactivates_retired_pending_months_back_to_pending(): void
    {
        Mail::fake();
        Notification::fake();
        Carbon::setTestNow('2026-03-15 10:00:00');

        try {
            $now = Carbon::now();
            $player = Player::factory()->create();
            $reactivatedGroup = TrainingGroup::query()->create([
                'school_id' => $this->school['id'],
                'name' => 'Grupo para retorno',
                'year' => $now->year,
                'category' => 'Todas las categorías',
                'days' => 'Jueves',
                'schedules' => '10:00AM - 11:00AM',
            ]);

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

            $payment = Payment::query()->where('inscription_id', $inscription->id)->where('year', $now->year)->firstOrFail();
            $nextMonthField = config('variables.KEY_INDEX_MONTHS')[$now->month + 1];
            $laterMonthField = config('variables.KEY_INDEX_MONTHS')[$now->month + 2];

            $payment->forceFill([
                $nextMonthField => Payment::$pending,
                "{$nextMonthField}_amount" => 50000,
                $laterMonthField => Payment::$paid,
                "{$laterMonthField}_amount" => 50000,
            ])->save();

            $this->actingAs($this->user);

            $this->post(route('inscriptions.destroy', [$inscription->id]), ['_method' => 'DELETE'])
                ->assertOk()
                ->assertJsonPath('success', true);

            $payment->refresh();
            $this->assertSame(Payment::$permanent_retirement, (int) $payment->{$nextMonthField});
            $this->assertSame(Payment::$paid, (int) $payment->{$laterMonthField});
            $this->assertSame(50000, (int) $payment->{"{$nextMonthField}_amount"});

            $response = $this->postJson(route('inscriptions.store'), [
                'unique_code' => $player->unique_code,
                'player_id' => $player->id,
                'start_date' => $now->copy()->addMonths(2)->format('Y-m-d'),
                'training_group_id' => $reactivatedGroup->id,
            ]);

            $response->assertOk();
            $response->assertJsonPath('success', true);
            $response->assertJsonPath('reactivated', true);

            $payment->refresh();

            $this->assertSame(Payment::$pending, (int) $payment->{$nextMonthField});
            $this->assertSame(50000, (int) $payment->{"{$nextMonthField}_amount"});
            $this->assertSame(Payment::$paid, (int) $payment->{$laterMonthField});
            $this->assertSame(50000, (int) $payment->{"{$laterMonthField}_amount"});
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_get_edit(): void
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

        $this->actingAs($this->user);

        $testResponse = $this->get(route('inscriptions.edit', [$inscription->unique_code]));

        $testResponse->assertStatus(200);

        $testResponse->assertJsonStructure([
            'id',
            'player_id',
            'competition_groups',
        ]);
    }

    public function test_get_edit_by_id(): void
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

        $this->actingAs($this->user);

        $testResponse = $this->get(route('inscriptions.edit', [$inscription->id]));

        $testResponse->assertStatus(200);
        $testResponse->assertJson([
            'id' => $inscription->id,
            'player_id' => $player->id,
            'brother_payment' => false,
            'monthly_payment_type' => Setting::MONTHLY_PAYMENT,
        ]);
        $testResponse->assertJsonStructure([
            'id',
            'player_id',
            'competition_groups',
            'brother_payment',
            'monthly_payment_type',
            'monthly_payment_amount',
        ]);
    }
}
