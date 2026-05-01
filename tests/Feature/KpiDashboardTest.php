<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Service\Kpi\KpiCacheService;
use Tests\TestCase;

final class KpiDashboardTest extends TestCase
{
    private const ATTENDANCE_FIELD_MAP = [
        0 => 'assistance_one',
        1 => 'assistance_two',
        2 => 'assistance_three',
        3 => 'assistance_four',
        4 => 'assistance_five',
        5 => 'assistance_six',
        6 => 'assistance_seven',
        7 => 'assistance_eight',
        8 => 'assistance_nine',
        9 => 'assistance_ten',
        10 => 'assistance_eleven',
        11 => 'assistance_twelve',
        12 => 'assistance_thirteen',
        13 => 'assistance_fourteen',
        14 => 'assistance_fifteen',
        15 => 'assistance_sixteen',
        16 => 'assistance_seventeen',
        17 => 'assistance_eighteen',
        18 => 'assistance_nineteen',
        19 => 'assistance_twenty',
        20 => 'assistance_twenty_one',
        21 => 'assistance_twenty_two',
        22 => 'assistance_twenty_three',
        23 => 'assistance_twenty_four',
        24 => 'assistance_twenty_five',
    ];

    public function test_kpis_endpoint_returns_professional_payload_and_refreshes_after_payment_changes(): void
    {
        $this->actingAs($this->user);

        $year = 2026;
        $month = 4;
        $groupA = $this->createTrainingGroup('Halcones');
        $groupB = $this->createTrainingGroup('Tigres');

        $playerA1 = $this->makePlayer('KPI-A1');
        $playerA2 = $this->makePlayer('KPI-A2');
        $playerB1 = $this->makePlayer('KPI-B1');
        $playerB2 = $this->makePlayer('KPI-B2');

        $inscriptionA1 = $this->createInscription($playerA1, $groupA, $year);
        $inscriptionA2 = $this->createInscription($playerA2, $groupA, $year);
        $inscriptionB1 = $this->createInscription($playerB1, $groupB, $year);
        $inscriptionB2 = $this->createInscription($playerB2, $groupB, $year);

        $this->createAssist($inscriptionA1, $groupA, $year, $month, [1, 1, 2]);
        $this->createAssist($inscriptionA2, $groupA, $year, $month, [1, 2]);
        $this->createAssist($inscriptionB1, $groupB, $year, $month, [1, 3]);
        $this->createAssist($inscriptionB2, $groupB, $year, $month, [1, 1]);

        $this->createPaymentRecord($inscriptionA1, $groupA, $year, [
            'enrollment' => ['status' => 1, 'amount' => 70000],
            'april' => ['status' => 1, 'amount' => 60000],
        ]);
        $paymentInDebt = $this->createPaymentRecord($inscriptionA2, $groupA, $year, [
            'april' => ['status' => 2, 'amount' => 60000],
        ]);
        $this->createPaymentRecord($inscriptionB1, $groupB, $year, [
            'april' => ['status' => 8, 'amount' => 60000],
        ]);
        $this->createPaymentRecord($inscriptionB2, $groupB, $year, [
            'april' => ['status' => 13, 'amount' => 60000],
        ]);

        $response = $this->getJson("/api/v2/kpis?year={$year}&month={$month}")
            ->assertOk();

        $cards = collect($response->json('summary_cards'))->keyBy('key');

        $this->assertEquals(60000, $cards['monthly_revenue']['value']);
        $this->assertEquals(70000, $cards['enrollment_revenue']['value']);
        $this->assertEquals(2.13, $cards['payment_compliance']['value']);
        $this->assertEquals(1, $cards['payments_debt']['value']);
        $this->assertEquals(66.67, $cards['attendance_percentage']['value']);
        $this->assertEquals(2, $cards['flagged_players']['value']);

        $this->assertSame(
            [$groupA->name, $groupB->name],
            $response->json('payment_group_report.categories')
        );
        $this->assertSame(
            ['Asistencias', 'Excusas', 'Ausencias', 'Retiros', 'Incapacidades'],
            $response->json('attendance_mix_report.categories')
        );
        $this->assertSame(12, count($response->json('monthly_trend_report.categories')));
        $this->assertNotEmpty($response->json('rankings.compliance'));
        $this->assertStringContainsString('/informes/asistencias?', $response->json('report_links.assists'));

        $paymentInDebt->april = 9;
        $paymentInDebt->april_amount = 60000;
        $paymentInDebt->save();

        $refreshedResponse = $this->getJson("/api/v2/kpis?year={$year}&month={$month}")
            ->assertOk();

        $refreshedCards = collect($refreshedResponse->json('summary_cards'))->keyBy('key');

        $this->assertEquals(120000, $refreshedCards['monthly_revenue']['value']);
        $this->assertEquals(4.26, $refreshedCards['payment_compliance']['value']);
        $this->assertEquals(0, $refreshedCards['payments_debt']['value']);
        $this->assertEquals(1, $refreshedCards['flagged_players']['value']);
    }

    public function test_instructor_kpis_only_expose_assigned_groups_and_scoped_options(): void
    {
        $year = 2026;
        $month = 4;
        $groupA = $this->createTrainingGroup('Halcones');
        $groupB = $this->createTrainingGroup('Tigres');
        $instructor = $this->createSchoolScopedUser(['instructor'], sprintf('kpi-instructor-%s@example.com', uniqid()));

        $groupA->instructors()->attach($instructor->id, ['assigned_year' => $year]);

        $inscriptionA = $this->createInscription($this->makePlayer('KPI-IA'), $groupA, $year);
        $inscriptionB = $this->createInscription($this->makePlayer('KPI-IB'), $groupB, $year);

        $this->createAssist($inscriptionA, $groupA, $year, $month, [1, 1]);
        $this->createAssist($inscriptionB, $groupB, $year, $month, [1, 1]);

        $this->createPaymentRecord($inscriptionA, $groupA, $year, [
            'april' => ['status' => 1, 'amount' => 50000],
        ]);
        $this->createPaymentRecord($inscriptionB, $groupB, $year, [
            'april' => ['status' => 2, 'amount' => 50000],
        ]);

        $this->actingAs($instructor)
            ->getJson("/api/v2/kpis?year={$year}&month={$month}")
            ->assertOk()
            ->assertJsonPath('payment_group_report.categories.0', $groupA->name)
            ->assertJsonCount(1, 'payment_group_report.categories')
            ->assertJsonCount(1, 'group_options')
            ->assertJsonPath('group_options.0.value', $groupA->id)
            ->assertJsonMissing(['label' => $groupB->name]);
    }

    public function test_kpi_cache_version_changes_when_assist_is_updated(): void
    {
        $this->actingAs($this->user);

        $group = $this->createTrainingGroup('Versionados');
        $inscription = $this->createInscription($this->makePlayer('KPI-CACHE'), $group, 2026);
        $assist = $this->createAssist($inscription, $group, 2026, 4, [1, 2]);

        $cacheService = app(KpiCacheService::class);
        $versionBefore = $cacheService->currentVersion($this->school['id']);

        $assist->assistance_two = 1;
        $assist->save();

        $this->assertSame($versionBefore + 1, $cacheService->currentVersion($this->school['id']));
    }

    private function createTrainingGroup(string $name): TrainingGroup
    {
        return TrainingGroup::query()->create([
            'name' => $name,
            'stage' => 'Formativo',
            'year' => (string) now()->year,
            'days' => 'lunes,miercoles',
            'schedules' => '08:00 - 09:00',
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
        ]);
    }

    private function makePlayer(string $uniqueCode): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => $uniqueCode,
            'category' => '2010-2011',
        ]);
    }

    private function createInscription(Player $player, TrainingGroup $group, int $year): Inscription
    {
        return Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $year,
            'start_date' => "{$year}-01-15",
            'category' => '2010-2011',
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);
    }

    private function createAssist(
        Inscription $inscription,
        TrainingGroup $group,
        int $year,
        int $month,
        array $statuses
    ): Assist {
        $payload = [
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year,
            'month' => $month,
        ];

        foreach ($statuses as $index => $status) {
            $field = self::ATTENDANCE_FIELD_MAP[$index] ?? null;

            if ($field) {
                $payload[$field] = $status;
            }
        }

        return Assist::query()->create($payload);
    }

    private function createPaymentRecord(
        Inscription $inscription,
        TrainingGroup $group,
        int $year,
        array $periods
    ): Payment {
        $statusFields = array_fill_keys(array_merge(['enrollment'], Payment::paymentFields()), 0);
        $amountFields = array_fill_keys(array_values(Payment::FIELD_AMOUNT_MAP), 0);

        foreach ($periods as $field => $config) {
            $statusFields[$field] = (int) ($config['status'] ?? 0);

            $amountField = Payment::amountFieldFor($field);
            if ($amountField) {
                $amountFields[$amountField] = (int) ($config['amount'] ?? 0);
            } elseif ($field === 'enrollment') {
                $amountFields['enrollment_amount'] = (int) ($config['amount'] ?? 0);
            }
        }

        $payment = Payment::query()->firstOrNew([
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year,
        ]);

        $payment->fill(array_merge($statusFields, $amountFields, [
            'school_id' => $this->school['id'],
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'unique_code' => $inscription->unique_code,
            'year' => $year,
        ]));
        $payment->save();

        return $payment->fresh();
    }

    private function createSchoolScopedUser(array $roles, string $email): User
    {
        $user = $this->createUser([
            'email' => $email,
            'school_id' => $this->school['id'],
        ], $roles);

        SchoolUser::query()->create([
            'user_id' => $user->id,
            'school_id' => $this->school['id'],
        ]);

        return $user;
    }
}
