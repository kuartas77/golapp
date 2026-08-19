<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Compatibility shim for deployments that still have this provider in a
 * previously generated configuration cache. It is intentionally not
 * registered by the current application configuration.
 */
final class EventServiceProvider extends ServiceProvider
{
    // Remove after every deployed environment has regenerated its config cache.
}
