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
    protected function setUp(): void
    {
        parent::setUp();

        $school = School::findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        foreach (['school.module.inscriptions', 'school.module.payments', 'school.module.attendances'] as $permission) {
            $permissions[$permission] = true;
        }
        $school->forceFill(['school_permissions' => $permissions])->save();
        School::forgetCachedSchool($school->id);
    }

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

    public function test_summary_attendance_identifies_principal_and_complementary_groups(): void
    {
        [$player, $inscription] = $this->createSummaryFixture(now()->year);
        $principalGroup = $inscription->trainingGroup;
        $principalGroup->update(['days' => 'Lunes']);
        $complementaryGroup = TrainingGroup::query()->create([
            'school_id' => $this->school['id'],
            'name' => 'Porteros resumen',
            'year' => now()->year,
            'year_active' => now()->year,
            'category' => 'Sub 10',
            'days' => 'Martes',
            'schedules' => '11:00AM - 12:00PM',
            'is_complementary' => true,
        ]);
        $inscription->update(['complementary_group_id' => $complementaryGroup->id]);
        $inscription->assistance()->withTrashed()->forceDelete();
        $principalAssist = Assist::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'training_group_id' => $principalGroup->id,
            'year' => now()->year,
            'month' => 1,
            'assistance_one' => 1,
        ]);
        $complementaryAssist = Assist::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $complementaryGroup->id,
            'year' => now()->year,
            'month' => 1,
        ], [
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'training_group_id' => $complementaryGroup->id,
            'year' => now()->year,
            'month' => 1,
            'assistance_one' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v2/inscriptions/{$inscription->id}/summary")
            ->assertOk()
            ->assertJsonPath('data.inscription.complementary_group.id', $complementaryGroup->id)
            ->assertJsonCount(2, 'data.attendance');

        $attendance = collect($response->json('data.attendance'))->keyBy('id');

        $this->assertSame('Grupo principal', $attendance[$principalAssist->id]['group_label']);
        $this->assertSame($principalGroup->id, $attendance[$principalAssist->id]['training_group_id']);
        $this->assertSame($principalGroup->name, $attendance[$principalAssist->id]['group_name']);
        $this->assertSame('Grupo complementario', $attendance[$complementaryAssist->id]['group_label']);
        $this->assertSame($complementaryGroup->id, $attendance[$complementaryAssist->id]['training_group_id']);
        $this->assertSame($complementaryGroup->name, $attendance[$complementaryAssist->id]['group_name']);
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

    public function test_previous_year_payment_can_be_updated(): void
    {
        [, $inscription, $payment] = $this->createSummaryFixture(now()->subYear()->year);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'january',
                'january' => Payment::$paid,
                'january_amount' => 50000,
            ])
            ->assertOk()
            ->assertJsonPath('data.january', Payment::$paid)
            ->assertJsonPath('data.january_amount', 50000);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'year' => now()->subYear()->year,
            'january' => Payment::$paid,
            'january_amount' => 50000,
        ]);
    }

    public function test_payment_update_does_not_expose_another_school(): void
    {
        $otherSchool = School::factory()->create();
        $group = $this->createTrainingGroup($otherSchool->id, now()->subYear()->year);
        $player = Player::factory()->create([
            'school_id' => $otherSchool->id,
            'unique_code' => 'OTHER-PAYMENT',
        ]);
        $inscription = Inscription::factory()->create([
            'school_id' => $otherSchool->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'year' => now()->subYear()->year,
        ]);
        $payment = Payment::query()->where('inscription_id', $inscription->id)->firstOrFail();

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->putJson("/api/v2/payments/{$payment->id}", [
                'column' => 'january',
                'january' => Payment::$paid,
                'january_amount' => 50000,
            ])
            ->assertNotFound();
    }

    public function test_historical_payments_can_be_queried_using_only_the_year(): void
    {
        [, $inscription, $payment] = $this->createSummaryFixture(now()->subYear()->year);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/payments?year='.now()->subYear()->year.'&dataRaw=true')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('rows.0.id', $payment->id)
            ->assertJsonPath('filter_options.categories.0.value', $inscription->category)
            ->assertJsonPath('filter_options.groups.0.value', $inscription->training_group_id);
    }

    public function test_current_year_payments_require_group_or_category_filter(): void
    {
        [, $inscription, $payment] = $this->createSummaryFixture(now()->year);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/payments?year='.now()->year.'&dataRaw=true')
            ->assertUnprocessable()
            ->assertJsonPath('errors.training_group_id.0', 'Para el año actual selecciona un grupo o una categoría.');

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/payments?'.http_build_query([
                'year' => now()->year,
                'training_group_id' => $inscription->training_group_id,
                'dataRaw' => true,
            ]))
            ->assertOk()
            ->assertJsonPath('rows.0.id', $payment->id);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/payments?'.http_build_query([
                'year' => now()->year,
                'category' => $inscription->category,
                'dataRaw' => true,
            ]))
            ->assertOk()
            ->assertJsonPath('rows.0.id', $payment->id);
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

    public function test_bulk_attendance_update_marks_only_active_loaded_assists(): void
    {
        $year = now()->year;
        $group = $this->createTrainingGroup((int) $this->school['id'], $year);
        $activeAssistOne = $this->createAssistForGroup($group, $year, 'BULK-ACTIVE-1');
        $activeAssistTwo = $this->createAssistForGroup($group, $year, 'BULK-ACTIVE-2');
        $retiredAssist = $this->createAssistForGroup($group, $year, 'BULK-RETIRED', true);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson('/api/v2/assists/bulk-update', [
                'assist_ids' => [
                    $activeAssistOne->id,
                    $activeAssistTwo->id,
                    $retiredAssist->id,
                ],
                'training_group_id' => $group->id,
                'month' => 1,
                'year' => $year,
                'column' => 'assistance_one',
                'value' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.requested_count', 3)
            ->assertJsonPath('data.updated_count', 2)
            ->assertJsonPath('data.skipped_count', 1);

        $this->assertSame(1, (int) $activeAssistOne->fresh()->assistance_one);
        $this->assertSame(1, (int) $activeAssistTwo->fresh()->assistance_one);
        $this->assertSame(2, (int) $retiredAssist->fresh()->assistance_one);
    }

    public function test_previous_year_bulk_attendance_update_is_blocked(): void
    {
        [, , , $assist] = $this->createSummaryFixture(now()->subYear()->year);

        $this->actingAs($this->user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson('/api/v2/assists/bulk-update', [
                'assist_ids' => [$assist->id],
                'training_group_id' => $assist->training_group_id,
                'month' => 1,
                'year' => $assist->year,
                'column' => 'assistance_one',
                'value' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.assist.0', 'Las asistencias de años anteriores son de sólo lectura.');

        $this->assertSame(2, (int) $assist->fresh()->assistance_one);
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

    private function createAssistForGroup(TrainingGroup $group, int $year, string $uniqueCode, bool $retired = false): Assist
    {
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => sprintf('%s-%s', $uniqueCode, uniqid()),
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

        if ($retired) {
            $inscription->delete();
        }

        return $assist;
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
