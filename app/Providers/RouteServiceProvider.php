<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
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
