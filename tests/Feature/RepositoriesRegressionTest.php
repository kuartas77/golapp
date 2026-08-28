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
    public function test_create_inscription_by_year_accepts_numeric_future_year_and_persists_records(): void
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
            'brother_payment' => true,
            'scholarship' => true,
            'scholarship_percentage' => Inscription::PARTIAL_SCHOLARSHIP_PERCENTAGE,
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
            'brother_payment' => true,
            'scholarship' => true,
            'scholarship_percentage' => Inscription::PARTIAL_SCHOLARSHIP_PERCENTAGE,
        ]);
    }

    public function test_group_pricing_year_renewal_starts_in_provisional_without_copying_the_previous_tariff(): void
    {
        $this->actingAs($this->user);

        $school = School::query()->findOrFail($this->school['id']);
        $school->update(['training_group_monthly_payment_enabled' => true]);
        $provisionalGroup = $school->trainingGroups()->where('name', 'Provisional')->firstOrFail();
        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => 'RC-GROUP-1001',
        ]);
        $actualYear = now()->year;
        $futureYear = now()->addYear()->year;

        Inscription::query()->create([
            'school_id' => $school->id,
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => $actualYear,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => 'SUB-13',
            'training_group_id' => $provisionalGroup->id,
            'competition_group_id' => null,
            'monthly_payment_type' => Inscription::TRAINING_GROUP_MONTHLY_PAYMENT,
            'monthly_payment_amount' => 85000,
        ]);

        app(InscriptionRepository::class)->createInscriptionByYear($actualYear, $futureYear);

        $renewedInscription = Inscription::query()
            ->where('school_id', $school->id)
            ->where('player_id', $player->id)
            ->where('year', $futureYear)
            ->firstOrFail();

        $this->assertSame($provisionalGroup->id, $renewedInscription->training_group_id);
        $this->assertTrue($renewedInscription->pre_inscription);
        $this->assertSame(Inscription::TRAINING_GROUP_MONTHLY_PAYMENT, $renewedInscription->monthly_payment_type);
        $this->assertNull($renewedInscription->monthly_payment_amount);
    }

    public function test_get_groups_year_respects_school_scope_when_using_or_filters(): void
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

    public function test_api_user_repository_paginates_by_authenticated_users_school(): void
    {
        $this->actingAs($this->user);

        $requestUser = User::query()->findOrFail($this->user->id);
        User::factory()->create(['school_id' => $requestUser->school_id]);
        $otherSchool = $this->createSchool();
        User::factory()->create(['school_id' => $otherSchool['id']]);

        $request = Request::create('/api/users', 'GET', ['per_page' => 50]);
        $request->setUserResolver(fn () => $requestUser);

        $repository = app(ApiUserRepository::class);
        $result = $repository->getUsersPaginate($request);

        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue(
            $result->getCollection()->every(
                fn (User $user) => (int) $user->school_id === (int) $requestUser->school_id
            )
        );
    }

    public function test_api_user_repository_uses_explicit_school_filter_and_per_page_clamp(): void
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
        $request->setUserResolver(fn () => $requestUser);

        $repository = app(ApiUserRepository::class);
        $result = $repository->getUsersPaginate($request);

        $this->assertSame(100, $result->perPage());
        $this->assertGreaterThan(0, $result->count());
        $this->assertTrue(
            $result->getCollection()->every(
                fn (User $user) => (int) $user->school_id === (int) $otherSchool['id']
            )
        );
    }
}
