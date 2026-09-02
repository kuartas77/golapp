<?php

declare(strict_types=1);

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckSettingNotification;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureBackofficeUser;
use App\Http\Middleware\EnsureGuardian;
use App\Http\Middleware\EnsureSchoolModuleView;
use App\Http\Middleware\EnsureSchoolPermission;
use App\Http\Middleware\HttpRedirect;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VerifySchool;
use App\Support\ExceptionReporter;
use Bepsvpt\SecureHeaders\SecureHeadersMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents(discover: false)
    ->withRouting(
        using: function (): void {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/notify')
                ->group(base_path('routes/notification.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->prefix('backoffice')
                ->group(base_path('routes/backoffice.php'));
        },
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            TrustHosts::class,
            HttpRedirect::class,
            SecureHeadersMiddleware::class,
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'ensure.backoffice-user' => EnsureBackofficeUser::class,
            'ensure.guardian' => EnsureGuardian::class,
            'role' => RoleMiddleware::class,
            'school.permission' => EnsureSchoolPermission::class,
            'school.module.view' => EnsureSchoolModuleView::class,
            'verified_school' => VerifySchool::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'check_notify_system' => CheckSettingNotification::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('queue:work --queue=golapp_default --stop-when-empty')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('optimize:if-changed')
            ->everyFiveMinutes()
            ->withoutOverlapping(5);

        $schedule->command('auth:clear-resets')->dailyAt('00:01')->withoutOverlapping();
        $schedule->command('sanctum:prune-expired --hours=24')->daily()->withoutOverlapping();
        $schedule->command('inscription:status')->dailyAt('05:05')->withoutOverlapping();
        $schedule->command('check:categories')->weeklyOn(0, '01:05')->withoutOverlapping();
        $schedule->command('assists:month')->lastDayOfMonth('23:00')->withoutOverlapping();
        $schedule->command('check:payments')->dailyAt('01:00')->withoutOverlapping();
        $schedule->command('charges:mark-due')->dailyAt('01:05')->withoutOverlapping();
        $schedule->command('update:payments')->lastDayOfMonth('00:02')->withoutOverlapping();
        $schedule->command('create:invoices')->lastDayOfMonth('00:10')->withoutOverlapping();
        $schedule->command('payments:monthly')->lastDayOfMonth('00:15')->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $exceptions->report(function (Throwable $exception): void {
            app(ExceptionReporter::class)->report($exception);
        });
    })
    ->create();
