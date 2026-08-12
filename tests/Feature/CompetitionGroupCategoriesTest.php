<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CompetitionGroup;
use App\Models\Tournament;
use Tests\TestCase;

final class CompetitionGroupCategoriesTest extends TestCase
{
    public function test_competition_group_accepts_multiple_categories_and_exposes_compatibility_fields(): void
    {
        $tournament = Tournament::query()->create([
            'name' => 'Copa Multicategoría',
            'school_id' => $this->school['id'],
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/competition_groups', [
                'name' => 'Selección A',
                'user_id' => $this->user->id,
                'tournament_id' => $tournament->id,
                'categories' => ['SUB-9', 'SUB-10'],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $group = CompetitionGroup::query()->where('name', 'Selección A')->firstOrFail();

        $this->assertSame(['SUB-9', 'SUB-10'], $group->categories);
        $this->assertSame('SUB-9, SUB-10', $group->category);
        $this->assertSame('SUB-9', $group->year);

        $this->actingAs($this->user)
            ->getJson("/api/v2/admin/competition_groups/{$group->id}")
            ->assertOk()
            ->assertJsonPath('data.categories.0', 'SUB-9')
            ->assertJsonPath('data.categories.1', 'SUB-10')
            ->assertJsonPath('data.category', 'SUB-9, SUB-10');
    }

    public function test_competition_group_validates_category_limits_and_accepts_legacy_payload(): void
    {
        $tournament = Tournament::query()->create([
            'name' => 'Copa Compatibilidad',
            'school_id' => $this->school['id'],
        ]);
        $basePayload = [
            'name' => 'Selección B',
            'user_id' => $this->user->id,
            'tournament_id' => $tournament->id,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/competition_groups', $basePayload + [
                'categories' => ['SUB-9', 'SUB-9'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categories.1');

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/competition_groups', $basePayload + [
                'categories' => collect(range(2, 14))->map(fn (int $age) => "SUB-{$age}")->all(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categories');

        $this->actingAs($this->user)
            ->postJson('/api/v2/admin/competition_groups', array_merge($basePayload, [
                'name' => 'Selección Legada',
                'year' => 'SUB-11',
            ]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $legacyGroup = CompetitionGroup::query()->where('name', 'Selección Legada')->firstOrFail();
        $this->assertSame(['SUB-11'], $legacyGroup->categories);
        $this->assertSame('SUB-11', $legacyGroup->category);
    }
}
