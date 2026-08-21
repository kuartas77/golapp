<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Notifications\PaymentNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class DebtNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_new_school_disables_automatic_debt_notifications_by_default(): void
    {
        $school = School::factory()->create();

        $this->assertFalse($school->fresh()->send_debt_notifications);
    }

    public function test_month_is_required_to_list_debtors(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/payments/debt-notifications?draw=1&start=0&length=10')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('month');
    }

    public function test_it_accepts_the_empty_search_object_sent_by_datatables(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/v2/payments/debt-notifications?'.http_build_query([
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'month' => 'january',
                'search' => [
                    'value' => '',
                    'regex' => 'false',
                ],
            ]))
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0);
    }

    public function test_it_lists_current_year_debt_and_partial_payment_with_school_scoped_filters(): void
    {
        $this->actingAs($this->user);
        [$debtPlayer, $debtPayment, $group] = $this->createDebtFixture([
            'unique_code' => 'DEBT-100',
            'names' => 'Laura Maria',
            'last_names' => 'Perez Gomez',
            'category' => 'SUB-12',
            'january' => Payment::$debt,
            'january_amount' => 65000,
        ]);
        [, $partialPayment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-200',
            'category' => 'SUB-14',
            'january' => Payment::$paid_,
            'january_amount' => 30000,
        ]);
        $this->createDebtFixture([
            'unique_code' => 'DEBT-OLD',
            'year' => now()->subYear()->year,
            'january' => Payment::$debt,
        ]);
        $otherSchool = School::factory()->create();
        $this->createDebtFixture([
            'school_id' => $otherSchool->id,
            'unique_code' => 'DEBT-OTHER-SCHOOL',
            'january' => Payment::$debt,
        ]);

        $response = $this->getJson('/api/v2/payments/debt-notifications?'.http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'month' => 'january',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonFragment([
                'payment_id' => $debtPayment->id,
                'unique_code' => 'DEBT-100',
                'player_name' => $debtPlayer->full_names,
                'category' => 'SUB-12',
                'training_group' => $group->name,
                'status' => Payment::$debt,
                'status_label' => 'Debe',
                'amount' => 65000,
                'can_notify' => true,
            ])
            ->assertJsonFragment([
                'payment_id' => $partialPayment->id,
                'status' => Payment::$paid_,
            ])
            ->assertJsonMissing(['unique_code' => 'DEBT-OLD'])
            ->assertJsonMissing(['unique_code' => 'DEBT-OTHER-SCHOOL']);

        $this->getJson('/api/v2/payments/debt-notifications?'.http_build_query([
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'month' => 'january',
            'search' => 'Laura DEBT-100',
            'category' => 'SUB-12',
            'training_group_id' => $group->id,
        ]))->assertOk()->assertJsonPath('recordsFiltered', 0);

        $this->getJson('/api/v2/payments/debt-notifications?'.http_build_query([
            'draw' => 3,
            'start' => 0,
            'length' => 10,
            'month' => 'january',
            'search' => 'Laura Maria',
            'category' => 'SUB-12',
            'training_group_id' => $group->id,
        ]))->assertOk()->assertJsonPath('recordsFiltered', 1);
    }

    public function test_it_marks_invalid_email_as_not_notifiable(): void
    {
        $this->actingAs($this->user);
        $this->createDebtFixture([
            'unique_code' => 'DEBT-NO-EMAIL',
            'email' => 'correo-invalido',
            'january' => Payment::$debt,
        ]);

        $this->getJson('/api/v2/payments/debt-notifications?draw=1&start=0&length=10&month=january')
            ->assertOk()
            ->assertJsonPath('data.0.can_notify', false)
            ->assertJsonMissingPath('data.0.email');
    }

    public function test_it_sends_one_or_many_existing_payment_notifications(): void
    {
        Notification::fake();
        $this->actingAs($this->user);
        [$firstPlayer, $firstPayment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-SEND-1',
            'january' => Payment::$debt,
        ]);
        [$secondPlayer, $secondPayment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-SEND-2',
            'january' => Payment::$paid_,
        ]);

        $this->postJson('/api/v2/payments/debt-notifications/send', [
            'month' => 'january',
            'payment_ids' => [$firstPayment->id, $secondPayment->id],
        ])
            ->assertOk()
            ->assertJsonPath('data.queued_count', 2)
            ->assertJsonPath('data.skipped_count', 0);

        Notification::assertSentTo($firstPlayer, PaymentNotification::class);
        Notification::assertSentTo($secondPlayer, PaymentNotification::class);

        $this->getJson('/api/v2/payments/debt-notifications?draw=1&start=0&length=10&month=january')
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 0)
            ->assertJsonMissing(['payment_id' => $firstPayment->id])
            ->assertJsonMissing(['payment_id' => $secondPayment->id]);

        $this->postJson('/api/v2/payments/debt-notifications/send', [
            'month' => 'january',
            'payment_ids' => [$firstPayment->id, $secondPayment->id],
        ])
            ->assertOk()
            ->assertJsonPath('data.queued_count', 0)
            ->assertJsonPath('data.skipped_count', 2);

        Notification::assertSentToTimes($firstPlayer, PaymentNotification::class, 1);
        Notification::assertSentToTimes($secondPlayer, PaymentNotification::class, 1);

        Carbon::setTestNow(now()->addDay()->startOfDay());

        $this->getJson('/api/v2/payments/debt-notifications?draw=2&start=0&length=10&month=january')
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 2);

        $this->postJson('/api/v2/payments/debt-notifications/send', [
            'month' => 'january',
            'payment_ids' => [$firstPayment->id, $secondPayment->id],
        ])
            ->assertOk()
            ->assertJsonPath('data.queued_count', 2)
            ->assertJsonPath('data.skipped_count', 0);

        Notification::assertSentToTimes($firstPlayer, PaymentNotification::class, 2);
        Notification::assertSentToTimes($secondPlayer, PaymentNotification::class, 2);
        Carbon::setTestNow();
    }

    public function test_existing_email_keeps_its_subject_and_all_debt_months(): void
    {
        [$player, $payment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-CONTENT',
            'january' => Payment::$debt,
            'february' => Payment::$paid_,
        ]);
        $school = School::query()->findOrFail($this->school['id']);
        $message = (new PaymentNotification($payment, $school))->toMail($player);
        $html = $message->render()->toHtml();

        $this->assertSame("Notificación pagos de mensualidades {$school->name}.", $message->subject);
        $this->assertStringContainsString('Enero', $html);
        $this->assertStringContainsString('Febrero', $html);
        $this->assertStringContainsString('Esperamos que te pongas al día con las obligaciones.', $html);
    }

    public function test_send_rejects_the_complete_batch_when_any_payment_is_not_eligible(): void
    {
        Notification::fake();
        $this->actingAs($this->user);
        [$eligiblePlayer, $eligiblePayment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-ELIGIBLE',
            'january' => Payment::$debt,
        ]);
        [, $notEligiblePayment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-NOT-ELIGIBLE',
            'january' => Payment::$paid,
        ]);

        $this->postJson('/api/v2/payments/debt-notifications/send', [
            'month' => 'january',
            'payment_ids' => [$eligiblePayment->id, $notEligiblePayment->id],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_ids');

        Notification::assertNothingSentTo($eligiblePlayer);
    }

    public function test_send_rejects_invalid_email_and_other_school_payments(): void
    {
        Notification::fake();
        $this->actingAs($this->user);
        [$invalidEmailPlayer, $invalidEmailPayment] = $this->createDebtFixture([
            'unique_code' => 'DEBT-INVALID-EMAIL',
            'email' => 'no-es-un-correo',
            'january' => Payment::$debt,
        ]);

        $this->postJson('/api/v2/payments/debt-notifications/send', [
            'month' => 'january',
            'payment_ids' => [$invalidEmailPayment->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('payment_ids');

        $otherSchool = School::factory()->create();
        [$otherPlayer, $otherPayment] = $this->createDebtFixture([
            'school_id' => $otherSchool->id,
            'unique_code' => 'DEBT-OTHER-SEND',
            'january' => Payment::$debt,
        ]);

        $this->postJson('/api/v2/payments/debt-notifications/send', [
            'month' => 'january',
            'payment_ids' => [$otherPayment->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('payment_ids');

        Notification::assertNothingSentTo($invalidEmailPlayer);
        Notification::assertNothingSentTo($otherPlayer);
    }

    public function test_automatic_command_only_sends_when_school_flag_is_enabled(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 16, 1));
        Notification::fake();
        $school = School::factory()->create([
            'is_enable' => true,
            'send_debt_notifications' => false,
        ]);
        [$player] = $this->createDebtFixture([
            'school_id' => $school->id,
            'year' => 2026,
            'unique_code' => 'DEBT-AUTOMATIC',
            'august' => Payment::$debt,
        ]);

        $this->artisan('check:payments')->assertSuccessful();
        Notification::assertNothingSentTo($player);

        $school->forceFill(['send_debt_notifications' => true])->save();
        Notification::fake();

        $this->artisan('check:payments')->assertSuccessful();
        Notification::assertSentTo($player, PaymentNotification::class);

        $this->artisan('check:payments')->assertSuccessful();
        Notification::assertSentToTimes($player, PaymentNotification::class, 1);

        Carbon::setTestNow();
    }

    private function createDebtFixture(array $overrides = []): array
    {
        $schoolId = (int) ($overrides['school_id'] ?? $this->school['id']);
        $year = (int) ($overrides['year'] ?? now()->year);
        $uniqueCode = (string) ($overrides['unique_code'] ?? fake()->unique()->bothify('DEBT-####'));
        $category = (string) ($overrides['category'] ?? 'SUB-10');
        $group = TrainingGroup::query()->create([
            'school_id' => $schoolId,
            'name' => 'Grupo '.$uniqueCode,
            'year' => $year,
            'category' => $category,
            'days' => 'Lunes',
            'schedules' => '08:00 - 09:00',
        ]);
        $player = Player::factory()->create([
            'school_id' => $schoolId,
            'unique_code' => $uniqueCode,
            'names' => $overrides['names'] ?? 'Jugador',
            'last_names' => $overrides['last_names'] ?? $uniqueCode,
            'email' => $overrides['email'] ?? "{$uniqueCode}@example.com",
        ]);
        $inscription = Inscription::factory()->create([
            'school_id' => $schoolId,
            'player_id' => $player->id,
            'unique_code' => $uniqueCode,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => $year,
            'start_date' => "{$year}-01-10",
            'category' => $category,
        ]);
        $payment = Payment::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => $year,
        ], array_merge([
            'school_id' => $schoolId,
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'unique_code' => $uniqueCode,
            'year' => $year,
            'enrollment' => Payment::$pending,
            'january' => Payment::$pending,
            'february' => Payment::$pending,
            'march' => Payment::$pending,
            'april' => Payment::$pending,
            'may' => Payment::$pending,
            'june' => Payment::$pending,
            'july' => Payment::$pending,
            'august' => Payment::$pending,
            'september' => Payment::$pending,
            'october' => Payment::$pending,
            'november' => Payment::$pending,
            'december' => Payment::$pending,
            'enrollment_amount' => 0,
            'january_amount' => 0,
            'february_amount' => 0,
            'march_amount' => 0,
            'april_amount' => 0,
            'may_amount' => 0,
            'june_amount' => 0,
            'july_amount' => 0,
            'august_amount' => 0,
            'september_amount' => 0,
            'october_amount' => 0,
            'november_amount' => 0,
            'december_amount' => 0,
        ], collect($overrides)->only(array_merge(Payment::paymentFields(), [
            'enrollment_amount',
            'january_amount',
            'february_amount',
            'march_amount',
            'april_amount',
            'may_amount',
            'june_amount',
            'july_amount',
            'august_amount',
            'september_amount',
            'october_amount',
            'november_amount',
            'december_amount',
        ]))->all()));

        return [$player, $payment, $group];
    }
}
