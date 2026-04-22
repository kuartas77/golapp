<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use Tests\TestCase;

final class CreateAssistsOnEndMonthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function testCommandCreatesNextMonthWithinSameYearOnRegularMonthEnd(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school, 2026);
        $inscription = $this->createInscription($school, $group, 2026);

        $this->artisan('assists:month', ['--date' => '2026-04-30'])->assertExitCode(0);

        $this->assertDatabaseHas('assists', [
            'inscription_id' => $inscription->id,
            'training_group_id' => $group->id,
            'year' => 2026,
            'month' => 5,
            'school_id' => $school->id,
        ]);
    }

    public function testCommandCreatesJanuaryOnlyForNextYearInscriptionsOnDecemberEnd(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school, 2026);
        $currentYearInscription = $this->createInscription($school, $group, 2026);
        $nextYearInscription = $this->createInscription($school, $group, 2027);

        $this->artisan('assists:month', ['--date' => '2026-12-31'])->assertExitCode(0);

        $this->assertDatabaseMissing('assists', [
            'inscription_id' => $currentYearInscription->id,
            'year' => 2027,
            'month' => 1,
        ]);

        $this->assertDatabaseHas('assists', [
            'inscription_id' => $nextYearInscription->id,
            'training_group_id' => $group->id,
            'year' => 2027,
            'month' => 1,
            'school_id' => $school->id,
        ]);
    }

    public function testCommandDoesNotCreateJanuaryWhenNextYearHasNoInscriptions(): void
    {
        $school = School::findOrFail($this->school['id']);
        $group = $this->createTrainingGroup($school, 2026);
        $this->createInscription($school, $group, 2026);

        $this->artisan('assists:month', ['--date' => '2026-12-31'])->assertExitCode(0);

        $this->assertDatabaseMissing('assists', [
            'training_group_id' => $group->id,
            'year' => 2027,
            'month' => 1,
            'school_id' => $school->id,
        ]);
    }

    private function createTrainingGroup(School $school, int $year): TrainingGroup
    {
        return TrainingGroup::query()->create([
            'name' => 'Command Team ' . fake()->unique()->numberBetween(100, 999),
            'year' => $year,
            'category' => ['Todas las categorías'],
            'days' => ['Lunes', 'Martes'],
            'schedules' => ['10:00AM - 11:00AM'],
            'school_id' => $school->id,
            'year_active' => $year,
        ]);
    }

    private function createInscription(School $school, TrainingGroup $group, int $year): Inscription
    {
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => 'CMD-' . fake()->unique()->numberBetween(1000, 9999),
        ]);

        return Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'school_id' => $school->id,
            'year' => $year,
            'start_date' => sprintf('%d-01-01', $year),
        ]);
    }
}
