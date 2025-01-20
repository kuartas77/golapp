<?php

declare(strict_types=1);

namespace Tests;

use App\Models\User;
use App\Models\School;
use App\Models\Setting;
use App\Models\SchoolUser;
use Spatie\Permission\Models\Role;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;

trait WithLogin
{
    use WithFaker;

    protected function createSettings(): void
    {
        Setting::insert(['key' => Setting::MAX_USERS]);
        Setting::insert(['key' => Setting::MAX_GROUPS]);
        Setting::insert(['key' => Setting::MAX_PLAYERS]);
        Setting::insert(['key' => Setting::MAX_INSCRIPTIONS]);
        Setting::insert(['key' => Setting::MAX_REGISTRATION_DATE]);
        Setting::insert(['key' => Setting::MIN_REGISTRATION_DATE]);
        Setting::insert(['key' => Setting::INSCRIPTION_AMOUNT]);
        Setting::insert(['key' => Setting::MONTHLY_PAYMENT]);
        Setting::insert(['key' => Setting::NOTIFY_PAYMENT_DAY]);
        Setting::insert(['key' => Setting::ANNUITY]);
    }

    protected function createRoles()
    {
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'school']);
        Role::create(['name' => 'instructor']);
    }

    protected function createUser(array $attributes = null, array $roles = [User::SCHOOL])
    {
        $user = User::factory()->create($attributes + ['password' => 'password']);
        $user->syncRoles($roles);
        $user->profile()->create();
        return $user;
    }

    protected function createSchool(array $attributes = null): array
    {
        $school = School::factory()->create($attributes);
        $school->schedules()->create([
            'schedule' => '10:00AM - 11:00AM',
        ]);

        $school->trainingGroups()->create([
            'name' => 'Provisional',
            'year' => now()->year,
            'category' => 'Todas las categorías',
            'days' => 'Grupo predeterminado',
            'schedules' => '10:00AM - 11:00AM',
        ]);
        return $school->toArray();
    }

    protected function createSchoolAndUser(array $attributes = null, array $roles = [User::SCHOOL]): array
    {
        $school = $this->createSchool($attributes);

        $user = $this->createUser([
            'email' => $school['email'],
            'school_id' => $school['id']
        ], roles: $roles);

        $schoolUser = new SchoolUser();
        $schoolUser->user_id = $user->id;
        $schoolUser->school_id = $school['id'];
        $schoolUser->save();

        return [$school, $user];
    }
}
