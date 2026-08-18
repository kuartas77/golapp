<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use Tests\TestCase;

final class InvoiceCreationInscriptionsTest extends TestCase
{
    public function test_it_returns_current_active_school_inscriptions_including_preinscriptions(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.inscriptions'] = false;
        $permissions['school.module.billing'] = true;
        $school->forceFill(['school_permissions' => School::normalizeSchoolPermissions($permissions)])->save();
        School::forgetCachedSchool($school->id);

        $group = TrainingGroup::query()->where('school_id', $school->id)->firstOrFail();
        $current = $this->createInscription($school, $group, 'Ana', 'Zuluaga', true);
        $regular = $this->createInscription($school, $group, 'Beatriz', 'Alvarez');
        $previousYear = $this->createInscription($school, $group, 'Carla', 'Bernal', year: now()->year - 1);
        $retired = $this->createInscription($school, $group, 'Diana', 'Castro');
        $retired->delete();

        [$otherSchoolData] = $this->createSchoolAndUser();
        $otherSchool = School::query()->findOrFail($otherSchoolData['id']);
        $otherGroup = TrainingGroup::query()->where('school_id', $otherSchool->id)->firstOrFail();
        $otherSchoolInscription = $this->createInscription($otherSchool, $otherGroup, 'Elena', 'Duque');

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/invoices/creation-inscriptions')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertSame([$regular->id, $current->id], $response->collect('data')->pluck('id')->all());
        $response->assertJsonFragment([
            'id' => $current->id,
            'unique_code' => $current->unique_code,
            'player_name' => 'Ana Zuluaga',
            'training_group_name' => $group->name,
        ]);
        $response->assertJsonMissing(['id' => $previousYear->id]);
        $response->assertJsonMissing(['id' => $retired->id]);
        $response->assertJsonMissing(['id' => $otherSchoolInscription->id]);
    }

    public function test_it_requires_the_billing_permission(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.billing'] = false;
        $school->forceFill(['school_permissions' => School::normalizeSchoolPermissions($permissions)])->save();
        School::forgetCachedSchool($school->id);

        $this->actingAs($this->user)
            ->getJson('/api/v2/invoices/creation-inscriptions')
            ->assertForbidden()
            ->assertJsonPath('message', 'No tienes permiso para acceder a este módulo.');
    }

    private function createInscription(
        School $school,
        TrainingGroup $group,
        string $names,
        string $lastNames,
        bool $preInscription = false,
        ?int $year = null,
    ): Inscription {
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'names' => $names,
            'last_names' => $lastNames,
        ]);

        return Inscription::factory()->create([
            'school_id' => $school->id,
            'player_id' => $player->id,
            'unique_code' => "INS-{$player->id}",
            'year' => $year ?? now()->year,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'pre_inscription' => $preInscription,
        ]);
    }
}
