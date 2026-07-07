<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\TrainingGroup;
use App\Service\Reports\DebtorReportService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class NPlusOneRegressionTest extends TestCase
{
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
