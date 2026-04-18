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
        foreach ([
            Setting::MAX_USERS,
            Setting::MAX_GROUPS,
            Setting::MAX_PLAYERS,
            Setting::MAX_INSCRIPTIONS,
            Setting::MAX_REGISTRATION_DATE,
            Setting::MIN_REGISTRATION_DATE,
            Setting::INSCRIPTION_AMOUNT,
            Setting::MONTHLY_PAYMENT,
            Setting::BROTHER_MONTHLY_PAYMENT,
            Setting::NOTIFY_PAYMENT_DAY,
            Setting::ANNUITY,
            Setting::SYSTEM_NOTIFY,
        ] as $key) {
            Setting::query()->firstOrCreate(['key' => $key], ['public' => false]);
        }
    }

    protected function createRoles()
    {
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'school']);
        Role::create(['name' => 'instructor']);
    }

    protected function createUser(array $attributes = [], array $roles = [User::SCHOOL])
    {
        $user = User::factory()->create($attributes + ['password' => 'password']);
        $user->syncRoles($roles);
        $user->profile()->create();
        return $user;
    }

    protected function createSchool(array $attributes = []): array
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

    protected function createSchoolAndUser( array $attributes = [], array $roles = [User::SCHOOL]): array
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
