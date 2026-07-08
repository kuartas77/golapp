<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $this->clearBootstrapCacheForTesting();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->ensureTestingDatabaseIsIsolated($app);

        return $app;
    }

    private function clearBootstrapCacheForTesting(): void
    {
        if (($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) !== 'testing') {
            return;
        }

        foreach (['config.php', 'events.php', 'routes-v7.php'] as $file) {
            $path = __DIR__.'/../bootstrap/cache/'.$file;

            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function ensureTestingDatabaseIsIsolated($app): void
    {
        if (($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) !== 'testing') {
            return;
        }

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Tests must use sqlite :memory:, got {$connection} ({$database}). Clear Laravel caches before running tests."
            );
        }
    }
}
