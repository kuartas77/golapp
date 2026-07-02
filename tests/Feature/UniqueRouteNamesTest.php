<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class UniqueRouteNamesTest extends BaseTestCase
{
    use CreatesApplication;

    public function test_route_names_are_unique(): void
    {
        $duplicates = collect($this->app['router']->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->duplicates()
            ->values()
            ->all();

        $this->assertSame([], $duplicates, 'Duplicate route names: '.implode(', ', $duplicates));
    }
}
