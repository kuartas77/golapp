<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use LazilyRefreshDatabase;
    use WithLogin;
    use WithPlayer;

    protected $user;

    protected $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRoles();
        $this->createSettings();
        $this->app->make(PermissionRegistrar::class)->registerPermissions($this->app->make(Gate::class));
        list($this->school, $this->user) = $this->createSchoolAndUser();
    }
}
