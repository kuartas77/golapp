<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Repositories\API\UserRepository as ApiUserRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\TrainingGroupRepository;
use Illuminate\Http\Request;
use Tests\TestCase;

final class RepositoriesRegressionTest extends TestCase
{
    public function testCreateInscriptionByYearAcceptsNumericFutureYearAndPersistsRecords(): void
    {
        $this->actingAs($this->user);

        $school = School::query()->findOrFail($this->school['id']);
        $trainingGroup = $school->trainingGroups()->firstOrFail();
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => 'RC-1001',
        ]);

        $actualYear = now()->year;
        $futureYear = now()->addYear()->year;

        Inscription::query()->create([
            'school_id' => $school->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $actualYear,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
        ]);

        $repository = app(InscriptionRepository::class);
        $repository->createInscriptionByYear($actualYear, $futureYear);

        $this->assertDatabaseHas('inscriptions', [
            'school_id' => $school->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $futureYear,
            'start_date' => sprintf('%d-01-01', $futureYear),
            'training_group_id' => $trainingGroup->id,
        ]);
    }

    public function testGetGroupsYearRespectsSchoolScopeWhenUsingOrFilters(): void
    {
        $this->actingAs($this->user);

        $year = now()->year;
        $mySchool = School::query()->findOrFail($this->school['id']);
        $otherSchool = $this->createSchool();

        $myGroup = TrainingGroup::query()->create([
            'name' => 'Group A',
            'stage' => 'Stage',
            'year' => (string) $year,
            'school_id' => $mySchool->id,
            'year_active' => $year,
        ]);

        $otherGroup = TrainingGroup::query()->create([
            'name' => 'Group B',
            'stage' => 'Stage',
            'year_two' => (string) $year,
            'school_id' => $otherSchool['id'],
            'year_active' => $year,
        ]);

        $repository = app(TrainingGroupRepository::class);
        $groups = $repository->getGroupsYear((string) $year);

        $this->assertArrayHasKey($myGroup->id, $groups->toArray());
        $this->assertArrayNotHasKey($otherGroup->id, $groups->toArray());
    }

    public function testApiUserRepositoryPaginatesByAuthenticatedUsersSchool(): void
    {
        $this->actingAs($this->user);

        $requestUser = User::query()->findOrFail($this->user->id);
        User::factory()->create(['school_id' => $requestUser->school_id]);
        $otherSchool = $this->createSchool();
        User::factory()->create(['school_id' => $otherSchool['id']]);

        $request = Request::create('/api/users', 'GET', ['per_page' => 50]);
        $request->setUserResolver(fn() => $requestUser);

        $repository = app(ApiUserRepository::class);
        $result = $repository->getUsersPaginate($request);

        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue(
            $result->getCollection()->every(
                fn(User $user) => (int) $user->school_id === (int) $requestUser->school_id
            )
        );
    }

    public function testApiUserRepositoryUsesExplicitSchoolFilterAndPerPageClamp(): void
    {
        $this->actingAs($this->user);

        $requestUser = User::query()->findOrFail($this->user->id);
        $otherSchool = $this->createSchool();

        User::factory()->create(['school_id' => $requestUser->school_id]);
        User::factory()->create(['school_id' => $otherSchool['id']]);

        $request = Request::create('/api/users', 'GET', [
            'school_id' => $otherSchool['id'],
            'per_page' => 999,
        ]);
        $request->setUserResolver(fn() => $requestUser);

        $repository = app(ApiUserRepository::class);
        $result = $repository->getUsersPaginate($request);

        $this->assertSame(100, $result->perPage());
        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue(
            $result->getCollection()->every(
                fn(User $user) => (int) $user->school_id === (int) $otherSchool['id']
            )
        );
    }
}
