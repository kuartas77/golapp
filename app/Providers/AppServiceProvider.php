<?php

namespace App\Providers;

use App\Service\Category\CategoryFormatService;
use App\Service\School\CurrentSchoolContext;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->scoped(CurrentSchoolContext::class, fn () => new CurrentSchoolContext);
        $this->app->scoped(CategoryFormatService::class, fn () => new CategoryFormatService);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->ensureTestingDatabaseIsIsolated();

        if ((bool) config('app.force_https')) {
            URL::forceScheme('https');
        }

        LogViewer::auth(function ($request) {
            return $request->user()
                && in_array($request->user()->email, [
                    'kuartas77@gmail.com',
                ]);
        });
    }

    private function ensureTestingDatabaseIsIsolated(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && $database === ':memory:') {
            return;
        }

        throw new RuntimeException(
            "Testing environment must use sqlite :memory:, got {$connection} ({$database})."
        );
    }
}
