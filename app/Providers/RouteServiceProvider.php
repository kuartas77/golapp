<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/inicio';
    public const PLAYER = 'portal/jugador';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::prefix('api/notify')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/notification.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->prefix('backoffice')
                ->group(base_path('routes/backoffice.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            $identity = hash('sha256', mb_strtolower(trim((string) $request->input('email', $request->input('username', '')))));

            return [
                Limit::perMinute(5)->by("auth-login:identity:{$identity}"),
                Limit::perMinute(30)->by('auth-login:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password-recovery', function (Request $request) {
            $identity = hash('sha256', mb_strtolower(trim((string) $request->input('email', ''))));

            return [
                Limit::perHour(3)->by("password-recovery:identity:{$identity}"),
                Limit::perHour(20)->by('password-recovery:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(10)
            ->by('password-reset:'.$request->ip()));

        RateLimiter::for('portal-otp-request', fn (Request $request) => Limit::perMinute(5)
            ->by('portal-otp-request:'.$request->ip()));

        RateLimiter::for('portal-otp-confirm', fn (Request $request) => Limit::perMinute(10)
            ->by('portal-otp-confirm:'.$request->ip()));

        RateLimiter::for('portal-inscription', function (Request $request) {
            $identity = hash('sha256', mb_strtolower(trim((string) $request->input('tutor_email', ''))));

            return [
                Limit::perHour(5)->by("portal-inscription:identity:{$identity}"),
                Limit::perHour(20)->by('portal-inscription:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('portal-client-errors', fn (Request $request) => Limit::perMinute(10)
            ->by('portal-client-errors:'.$request->ip()));
    }
}
