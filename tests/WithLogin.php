<?php

namespace Tests;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;

trait WithLogin
{
    use WithFaker;

    protected function createUser(array $attributes = null): array
    {
        $user = User::factory()->create($attributes + ['password' => 'password']);

        return $user->toArray();
    }

    protected function createSchool(array $attributes = null): array
    {
        $school = School::factory()->create($attributes);
        return $school->toArray();
    }

    protected function createSchoolAndUser(array $attributes = null): array
    {
        $school = $this->createSchool($attributes);
        $user = $this->createUser([
            'email' => $school['email'], 
            'school_id' => $school['id']
        ]);

        $relationSchool = new SchoolUser();
        $relationSchool->user_id = $user['id'];
        $relationSchool->school_id = $school['id'];
        $relationSchool->save();

        return [$school, $user];
    }
}