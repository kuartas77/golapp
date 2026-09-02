<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Service\Inscription\InscriptionPaymentService;
use App\Service\Reports\DebtorReportService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class NPlusOneRegressionTest extends TestCase
{
    public function test_inscription_mutations_load_complementary_groups_only_once_per_request(): void
    {
        Notification::fake();

        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'N1-COMPLEMENTARY-GROUPS',
        ]);
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $relationQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$relationQueries): void {
            $sql = str_replace(['`', '"'], '', strtolower($query->sql));

            if (str_contains($sql, 'from training_groups inner join complementary_training_group_inscription')) {
                $relationQueries++;
            }
        });

        $this->actingAs($this->user)
            ->postJson(route('inscriptions.store'), [
                'unique_code' => $player->unique_code,
                'player_id' => $player->id,
                'start_date' => now()->format('Y-m-d'),
                'training_group_id' => $group->id,
            ])
            ->assertOk();

        $inscription = Inscription::query()->where('player_id', $player->id)->firstOrFail();

        $this->actingAs($this->user)
            ->putJson(route('inscriptions.update', $inscription), [
                'unique_code' => $player->unique_code,
                'player_id' => $player->id,
                'start_date' => now()->format('Y-m-d'),
                'training_group_id' => $group->id,
            ])
            ->assertOk();

        $this->assertSame(1, $relationQueries);
    }

    public function test_inscription_updates_do_not_duplicate_the_generated_payment(): void
    {
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'N1-OBSERVER',
        ]);
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        $inscription = Inscription::factory()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);
        $inscription->update(['category' => 'SUB-12']);

        $this->assertSame(1, Payment::query()->where('inscription_id', $inscription->id)->count());
    }

    public function test_group_tariff_transition_reuses_the_target_group_query(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->update(['training_group_monthly_payment_enabled' => true]);
        $provisional = TrainingGroup::query()
            ->where('school_id', $school->id)
            ->where('name', 'Provisional')
            ->firstOrFail();
        $target = TrainingGroup::query()->create([
            'school_id' => $school->id,
            'name' => 'Grupo tarifa N+1',
            'year_active' => now()->year,
            'is_complementary' => false,
            'monthly_payment_amount' => 90000,
        ]);
        $player = Player::factory()->create(['school_id' => $school->id]);
        $inscription = Inscription::factory()->create([
            'school_id' => $school->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $provisional->id,
            'competition_group_id' => null,
            'monthly_payment_type' => Inscription::TRAINING_GROUP_MONTHLY_PAYMENT,
            'monthly_payment_amount' => null,
        ]);
        $requestData = [
            'school_id' => $school->id,
            'training_group_id' => $target->id,
        ];
        $trainingGroupQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$trainingGroupQueries): void {
            $sql = str_replace(['`', '"'], '', strtolower($query->sql));

            if (str_contains($sql, 'from training_groups')) {
                $trainingGroupQueries++;
            }
        });

        $service = app(InscriptionPaymentService::class);
        $this->assertTrue($service->shouldInitializeGroupTariff($requestData, $inscription, $school));
        $service->prepareMonthlyPaymentData($requestData, $school);

        $this->assertSame(2, $trainingGroupQueries);
        $this->assertSame(90000, $requestData['monthly_payment_amount']);
    }

    public function test_player_debt_lookup_does_not_reload_player_relations_for_each_year(): void
    {
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'N1-CLEARANCE',
        ]);
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        foreach ([now()->year - 1, now()->year] as $year) {
            $inscription = Inscription::factory()->create([
                'school_id' => $this->school['id'],
                'player_id' => $player->id,
                'unique_code' => $player->unique_code,
                'year' => $year,
                'training_group_id' => $group->id,
                'competition_group_id' => null,
            ]);

            $paymentValues = [];

            foreach (Payment::paymentFields() as $field) {
                $paymentValues[$field] = Payment::$pending;
                $paymentValues[Payment::amountFieldFor($field)] = 0;
            }

            $paymentValues['january'] = Payment::$debt;
            $paymentValues['january_amount'] = 50000;

            Payment::query()->where('inscription_id', $inscription->id)->update($paymentValues);
        }

        $playerQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$playerQueries): void {
            if (str_contains(strtolower($query->sql), 'from `players`')) {
                $playerQueries++;
            }
        });

        $debts = app(DebtorReportService::class)->playerDebts(
            (int) $this->school['id'],
            (int) $player->id,
            Carbon::now(),
        );

        $this->assertCount(2, $debts);
        $this->assertSame(100000.0, (float) $debts->sum('amount'));
        $this->assertSame(0, $playerQueries);
    }
}
