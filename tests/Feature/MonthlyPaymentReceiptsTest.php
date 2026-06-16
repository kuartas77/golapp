<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\TrainingGroup;
use Illuminate\Support\Arr;
use Tests\TestCase;

final class MonthlyPaymentReceiptsTest extends TestCase
{
    public function test_it_lists_only_closed_paid_monthly_receipts(): void
    {
        $this->actingAs($this->user);
        [, $payment] = $this->createReceiptFixture([
            'january' => Payment::$paid_cash,
            'january_amount' => 55000,
            'february' => Payment::$paid_deposit,
            'february_amount' => 56000,
            'march' => Payment::$paid_,
            'march_amount' => 20000,
            'april' => Payment::$payment_agreement,
            'april_amount' => 40000,
            'may' => Payment::$debt,
            'may_amount' => 50000,
            'enrollment' => Payment::$paid,
            'enrollment_amount' => 70000,
        ]);

        $response = $this->getJson('/api/v2/payments/monthly-receipts?year='.now()->year);

        $response
            ->assertOk()
            ->assertJsonPath('data.count', 2)
            ->assertJsonPath('data.rows.0.payment_id', $payment->id)
            ->assertJsonPath('data.rows.0.month', 'january')
            ->assertJsonPath('data.rows.0.amount', 55000)
            ->assertJsonPath('data.rows.1.month', 'february');

        $months = collect($response->json('data.rows'))->pluck('month')->all();
        $this->assertNotContains('march', $months);
        $this->assertNotContains('april', $months);
        $this->assertNotContains('enrollment', $months);
    }

    public function test_it_filters_receipts_by_unique_code_group_category_and_year(): void
    {
        $this->actingAs($this->user);
        [$matchingInscription, $matchingPayment, $matchingGroup] = $this->createReceiptFixture([
            'unique_code' => 'RCPT-100',
            'category' => '2012-2013',
            'january' => Payment::$paid,
            'january_amount' => 51000,
        ]);
        $this->createReceiptFixture([
            'unique_code' => 'RCPT-200',
            'category' => '2014-2015',
            'january' => Payment::$paid,
            'january_amount' => 52000,
        ]);
        $this->createReceiptFixture([
            'unique_code' => 'RCPT-300',
            'category' => $matchingInscription->category,
            'year' => now()->subYear()->year,
            'january' => Payment::$paid,
            'january_amount' => 53000,
        ]);

        $response = $this->getJson('/api/v2/payments/monthly-receipts?'.http_build_query([
            'year' => now()->year,
            'unique_code' => $matchingPayment->unique_code,
            'training_group_id' => $matchingGroup->id,
            'category' => $matchingInscription->category,
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.rows.0.unique_code', 'RCPT-100')
            ->assertJsonPath('data.rows.0.category', '2012-2013')
            ->assertJsonPath('data.rows.0.month', 'january');
    }

    public function test_it_filters_receipts_by_player_name(): void
    {
        $this->actingAs($this->user);
        $this->createReceiptFixture([
            'unique_code' => 'RCPT-NAME-100',
            'player_names' => 'Carlos Alberto',
            'player_last_names' => 'Montoya Perez',
            'january' => Payment::$paid,
            'january_amount' => 51000,
        ]);
        $this->createReceiptFixture([
            'unique_code' => 'RCPT-NAME-200',
            'player_names' => 'Andres Felipe',
            'player_last_names' => 'Gomez Ruiz',
            'january' => Payment::$paid,
            'january_amount' => 52000,
        ]);

        $response = $this->getJson('/api/v2/payments/monthly-receipts?'.http_build_query([
            'year' => now()->year,
            'player_name' => 'Carlos Alberto Montoya',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.rows.0.unique_code', 'RCPT-NAME-100');
    }

    public function test_it_paginates_receipts_for_datatables_server_side(): void
    {
        $this->actingAs($this->user);

        for ($index = 1; $index <= 8; $index++) {
            $this->createReceiptFixture([
                'unique_code' => sprintf('RCPT-DT-%03d', $index),
                'january' => Payment::$paid,
                'january_amount' => 51000,
                'february' => Payment::$paid_cash,
                'february_amount' => 52000,
            ]);
        }

        $response = $this->getJson('/api/v2/payments/monthly-receipts?'.http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'year' => now()->year,
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ],
            'columns' => [
                ['data' => 'player_name', 'name' => 'player_name', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'month_label', 'name' => 'month_order', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'year', 'name' => 'year', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'status_label', 'name' => 'status', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'amount', 'name' => 'amount', 'searchable' => 'true', 'orderable' => 'true'],
                ['data' => 'pdf_url', 'name' => 'payment_id', 'searchable' => 'false', 'orderable' => 'false'],
            ],
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('draw', 1)
            ->assertJsonPath('recordsTotal', 16)
            ->assertJsonPath('recordsFiltered', 16);

        $this->assertCount(10, $response->json('data'));
        $this->assertArrayHasKey('pdf_url', $response->json('data.0'));
        $this->assertArrayHasKey('status_label', $response->json('data.0'));
    }

    public function test_it_filters_datatables_receipts_by_player_name_server_side(): void
    {
        $this->actingAs($this->user);
        $this->createReceiptFixture([
            'unique_code' => 'RCPT-DT-NAME-100',
            'player_names' => 'Lucia Maria',
            'player_last_names' => 'Salazar Mora',
            'january' => Payment::$paid,
            'january_amount' => 51000,
        ]);
        $this->createReceiptFixture([
            'unique_code' => 'RCPT-DT-NAME-200',
            'player_names' => 'Mateo',
            'player_last_names' => 'Rios',
            'january' => Payment::$paid,
            'january_amount' => 52000,
        ]);

        $response = $this->getJson('/api/v2/payments/monthly-receipts?'.http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'year' => now()->year,
            'player_name' => 'Lucia Maria Salazar',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.unique_code', 'RCPT-DT-NAME-100');
    }

    public function test_it_streams_a_valid_monthly_receipt_pdf(): void
    {
        $this->actingAs($this->user);
        [, $payment] = $this->createReceiptFixture([
            'january' => Payment::$paid_cash,
            'january_amount' => 55000,
        ]);

        $this->get(route('payments.monthly-receipts.show', [
            'payment' => $payment->id,
            'month' => 'january',
        ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_it_rejects_invalid_or_unpaid_receipt_pdf_requests(): void
    {
        $this->actingAs($this->user);
        [, $payment] = $this->createReceiptFixture([
            'january' => Payment::$debt,
            'january_amount' => 55000,
            'february' => Payment::$paid_,
            'february_amount' => 20000,
            'march' => Payment::$payment_agreement,
            'march_amount' => 40000,
            'enrollment' => Payment::$paid,
            'enrollment_amount' => 70000,
        ]);

        $this->get(route('payments.monthly-receipts.show', [
            'payment' => $payment->id,
            'month' => 'january',
        ]))->assertNotFound();

        $this->get(route('payments.monthly-receipts.show', [
            'payment' => $payment->id,
            'month' => 'february',
        ]))->assertNotFound();

        $this->get(route('payments.monthly-receipts.show', [
            'payment' => $payment->id,
            'month' => 'march',
        ]))->assertNotFound();

        $this->get(route('payments.monthly-receipts.show', [
            'payment' => $payment->id,
            'month' => 'enrollment',
        ]))->assertNotFound();
    }

    public function test_it_rejects_receipts_from_other_schools(): void
    {
        $this->actingAs($this->user);
        [$otherSchool] = $this->createSchoolAndUser(['email' => 'other-school@example.com']);
        [, $payment] = $this->createReceiptFixture([
            'school_id' => $otherSchool['id'],
            'unique_code' => 'OTHER-100',
            'january' => Payment::$paid_cash,
            'january_amount' => 55000,
        ]);

        $this->get(route('payments.monthly-receipts.show', [
            'payment' => $payment->id,
            'month' => 'january',
        ]))->assertNotFound();

        $this->getJson('/api/v2/payments/monthly-receipts?'.http_build_query([
            'year' => now()->year,
            'unique_code' => $payment->unique_code,
        ]))
            ->assertOk()
            ->assertJsonPath('data.count', 0);
    }

    private function createReceiptFixture(array $overrides = []): array
    {
        $schoolId = (int) ($overrides['school_id'] ?? $this->school['id']);
        $year = (int) ($overrides['year'] ?? now()->year);
        $uniqueCode = (string) ($overrides['unique_code'] ?? sprintf('RCPT-%s', uniqid()));
        $category = (string) ($overrides['category'] ?? '2010-2011');
        $group = $overrides['training_group'] ?? TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->where('year', $year)
            ->first();

        if (! $group) {
            $group = TrainingGroup::query()->create([
                'school_id' => $schoolId,
                'name' => 'Grupo Recibos',
                'year' => $year,
                'category' => $category,
                'days' => 'Lunes',
                'schedules' => '08:00 - 09:00',
            ]);
        }

        $player = Player::factory()->create([
            'school_id' => $schoolId,
            'unique_code' => $uniqueCode,
            'names' => $overrides['player_names'] ?? 'Jugador',
            'last_names' => $overrides['player_last_names'] ?? $uniqueCode,
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

        $paymentOverrides = Arr::only($overrides, array_merge(Payment::paymentFields(), [
            'school_id',
            'unique_code',
            'year',
            'training_group_id',
            'inscription_id',
            'enrollment',
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
        ]));

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
            'enrollment' => Payment::$debt,
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
        ], $paymentOverrides));

        return [$inscription, $payment, $group];
    }
}
