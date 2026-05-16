<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Models\User;
use Tests\TestCase;

final class InscriptionSummaryTest extends TestCase
{
    public function test_school_user_can_fetch_current_year_summary(): void
    {
        [$player, $inscription, $payment, $assist] = $this->createSummaryFixture(now()->year);
        $this->createSummaryFixture(now()->subYear()->year, $player);

        $this->actingAs($this->user)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/summary")
            ->assertOk()
            ->assertJsonPath('data.can_edit', true)
            ->assertJsonPath('data.inscription.id', $inscription->id)
            ->assertJsonPath('data.inscription.status', 'active')
            ->assertJsonPath('data.inscription.status_label', 'Activa')
            ->assertJsonPath('data.player.id', $player->id)
            ->assertJsonPath('data.payments.0.id', $payment->id)
            ->assertJsonPath('data.attendance.0.id', $assist->id)
            ->assertJsonCount(2, 'data.years')
            ->assertJsonPath('data.years.0.status_label', 'Activa')
            ->assertJsonPath('data.years.1.status_label', 'Histórica');
    }

    public function test_previous_year_summary_is_read_only(): void
    {
        [, $inscription] = $this->createSummaryFixture(now()->subYear()->year);

        $this->actingAs($this->user)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/summary")
            ->assertOk()
            ->assertJsonPath('data.can_edit', false)
            ->assertJsonPath('data.inscription.year', now()->subYear()->year)
            ->assertJsonPath('data.inscription.status', 'historical')
            ->assertJsonPath('data.inscription.status_label', 'Histórica');
    }

    public function test_instructor_cannot_fetch_inscription_summary(): void
    {
        [, $inscription] = $this->createSummaryFixture(now()->year);
        $instructor = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => sprintf('summary-instructor-%s@example.com', uniqid()),
        ], [User::INSTRUCTOR]);

        $this->actingAs($instructor)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/summary")
            ->assertForbidden();
    }

    public function test_summary_does_not_expose_other_school_inscriptions(): void
    {
        $otherSchool = School::factory()->create();
        $group = $this->createTrainingGroup($otherSchool->id, now()->year);
        $player = Player::factory()->create([
            'school_id' => $otherSchool->id,
            'unique_code' => 'OTHER-SUMMARY',
        ]);
        $inscription = Inscription::factory()->create([
            'school_id' => $otherSchool->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => now()->year,
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/summary")
            ->assertNotFound();
    }

    public function test_previous_year_payment_update_is_blocked(): void
    {
        [, , $payment] = $this->createSummaryFixture(now()->subYear()->year);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'january',
                'january' => Payment::$paid,
                'january_amount' => 50000,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.payment.0', 'Las mensualidades de años anteriores son de sólo lectura.');
    }

    public function test_previous_year_attendance_update_is_blocked(): void
    {
        [, , , $assist] = $this->createSummaryFixture(now()->subYear()->year);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson("/api/v2/assists/{$assist->id}", [
                'id' => $assist->id,
                'assistance_one' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.assist.0', 'Las asistencias de años anteriores son de sólo lectura.');
    }

    private function createSummaryFixture(int $year, ?Player $player = null): array
    {
        $group = $this->createTrainingGroup((int) $this->school['id'], $year);
        $player ??= Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => sprintf('SUMMARY-%s', uniqid()),
        ]);

        $inscription = Inscription::factory()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => $year,
            'start_date' => "{$year}-01-10",
            'category' => 'Sub 10',
        ]);

        $payment = Payment::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => $year,
        ], [
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'unique_code' => $player->unique_code,
            'year' => $year,
            'enrollment' => Payment::$debt,
            'january' => Payment::$debt,
            'enrollment_amount' => 70000,
            'january_amount' => 50000,
        ]);

        $assist = Assist::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => $year,
            'month' => 1,
        ], [
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => $year,
            'month' => 1,
            'assistance_one' => 2,
        ]);

        return [$player, $inscription, $payment, $assist];
    }

    private function createTrainingGroup(int $schoolId, int $year): TrainingGroup
    {
        return TrainingGroup::query()->create([
            'school_id' => $schoolId,
            'name' => sprintf('Resumen %s %s', $year, uniqid()),
            'year' => $year,
            'year_active' => $year,
            'category' => 'Sub 10',
            'days' => 'Lunes',
            'schedules' => '10:00AM - 11:00AM',
        ]);
    }
}
