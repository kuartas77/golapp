<?php

namespace App\Providers;

use App\Service\School\CurrentSchoolContext;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ((bool) config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
