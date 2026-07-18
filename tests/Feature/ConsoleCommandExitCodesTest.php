<?php

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use App\Models\TrainingGroup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ConsoleCommandExitCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_payments_returns_a_success_exit_code(): void
    {
        $this->artisan('check:payments')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_check_categories_returns_a_success_exit_code(): void
    {
        $this->artisan('check:categories')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_update_payments_records_history_when_pending_becomes_debt(): void
    {
        Carbon::setTestNow('2026-01-31 00:02:00');

        try {
            $school = School::query()->findOrFail($this->school['id']);
            $school->forceFill(['is_enable' => true])->save();
            $school->settingsValues()->where('setting_key', Setting::MONTHLY_PAYMENT)->update(['value' => '57000']);

            $trainingGroup = TrainingGroup::query()
                ->where('school_id', $school->id)
                ->where('is_complementary', false)
                ->firstOrFail();
            $player = Player::factory()->create();
            $inscription = Inscription::factory()->create([
                'player_id' => $player->id,
                'unique_code' => $player->unique_code,
                'year' => 2026,
                'training_group_id' => $trainingGroup->id,
                'competition_group_id' => null,
                'start_date' => '2026-01-01',
                'category' => categoriesName(Carbon::parse($player->date_birth)->year),
                'school_id' => $school->id,
                'monthly_payment_type' => Setting::MONTHLY_PAYMENT,
                'monthly_payment_amount' => 57000,
            ]);
            $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

            $payment->forceFill([
                'february' => Payment::$pending,
                'february_amount' => 0,
                'march' => Payment::$paid_cash,
                'march_amount' => 57000,
            ])->save();

            $this->artisan('update:payments')
                ->assertExitCode(Command::SUCCESS);

            $payment->refresh();

            $this->assertSame(Payment::$debt, (int) $payment->february);
            $this->assertSame(57000, (int) $payment->february_amount);
            $this->assertDatabaseHas('payment_change_logs', [
                'payment_id' => $payment->id,
                'inscription_id' => $inscription->id,
                'field' => 'february',
                'old_status' => Payment::$pending,
                'new_status' => Payment::$debt,
                'old_amount' => 0,
                'new_amount' => 57000,
                'source' => 'monthly_job',
            ]);
            $this->assertDatabaseMissing('payment_change_logs', [
                'payment_id' => $payment->id,
                'field' => 'march',
                'source' => 'monthly_job',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_optimize_if_changed_returns_success_after_generating_caches(): void
    {
        try {
            $this->artisan('optimize:if-changed')
                ->assertExitCode(Command::SUCCESS);
        } finally {
            Artisan::call('optimize:clear');
        }
    }
}
