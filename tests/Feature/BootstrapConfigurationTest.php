<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckSettingNotification;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureGuardian;
use App\Http\Middleware\EnsureSchoolPermission;
use App\Http\Middleware\HttpRedirect;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VerifySchool;
use App\Mail\ErrorLog;
use App\Models\User;
use App\Providers\EventServiceProvider;
use Bepsvpt\SecureHeaders\SecureHeadersMiddleware;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use RuntimeException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Tests\TestCase;

class BootstrapConfigurationTest extends TestCase
{
    public function test_application_uses_the_modern_laravel_kernels_and_exception_handler(): void
    {
        $this->assertSame(HttpKernel::class, $this->app->make(HttpKernelContract::class)::class);
        $this->assertSame(ConsoleKernel::class, $this->app->make(ConsoleKernelContract::class)::class);
        $this->assertSame(Handler::class, $this->app->make(ExceptionHandler::class)::class);
    }

    public function test_http_middleware_contract_is_preserved_in_bootstrap(): void
    {
        /** @var HttpKernel $kernel */
        $kernel = $this->app->make(HttpKernelContract::class);

        $expectedGlobalMiddleware = [
            TrustHosts::class,
            HttpRedirect::class,
            SecureHeadersMiddleware::class,
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ];

        $this->assertSame(
            $expectedGlobalMiddleware,
            array_slice($kernel->getGlobalMiddleware(), 0, count($expectedGlobalMiddleware))
        );

        $this->assertSame([
            'web' => [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ],
            'api' => [
                EnsureFrontendRequestsAreStateful::class,
                'throttle:api',
                SubstituteBindings::class,
            ],
        ], $kernel->getMiddlewareGroups());

        $aliases = $kernel->getMiddlewareAliases();

        $this->assertSame(Authenticate::class, $aliases['auth']);
        $this->assertSame(RedirectIfAuthenticated::class, $aliases['guest']);
        $this->assertSame(EnsureGuardian::class, $aliases['ensure.guardian']);
        $this->assertSame(RoleMiddleware::class, $aliases['role']);
        $this->assertSame(EnsureSchoolPermission::class, $aliases['school.permission']);
        $this->assertSame(VerifySchool::class, $aliases['verified_school']);
        $this->assertSame(CheckAbilities::class, $aliases['abilities']);
        $this->assertSame(CheckForAnyAbility::class, $aliases['ability']);
        $this->assertSame(CheckSettingNotification::class, $aliases['check_notify_system']);
    }

    public function test_all_custom_route_entrypoints_are_loaded_once(): void
    {
        $routes = collect($this->app['router']->getRoutes()->getRoutes());

        $this->assertTrue($routes->contains(fn ($route) => str_starts_with($route->uri(), 'api/v2/')));
        $this->assertTrue($routes->contains(fn ($route) => str_starts_with($route->uri(), 'api/notify/')));
        $this->assertTrue($routes->contains(fn ($route) => str_starts_with($route->uri(), 'backoffice/')));

        $signatures = $routes->map(fn ($route) => implode('|', $route->methods()).' '.$route->uri());

        $this->assertSame([], $signatures->duplicates()->values()->all());
    }

    public function test_exception_reporting_and_duplicate_suppression_are_preserved(): void
    {
        Mail::fake();
        $this->createUser(roles: [User::SUPER_ADMIN]);

        $exception = new RuntimeException('bootstrap report contract');
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($exception);
        $handler->report($exception);

        Mail::assertSent(ErrorLog::class, 1);
        Mail::assertSent(ErrorLog::class, function (ErrorLog $mail): bool {
            return $mail->message === 'Unhandled exception [RuntimeException]'
                && $mail->context['error'] === 'bootstrap report contract'
                && $mail->context['context'] === 'console';
        });
    }

    public function test_event_bootstrap_does_not_duplicate_or_reactivate_legacy_listeners(): void
    {
        $listenersBeforeCompatibilityProvider = $this->app['events']->getRawListeners();

        $this->app->register(EventServiceProvider::class, force: true);

        $listeners = $this->app['events']->getRawListeners();

        $this->assertCount(1, $listeners[Registered::class] ?? []);
        $this->assertArrayNotHasKey(MessageSent::class, $listeners);
        $this->assertSame($listenersBeforeCompatibilityProvider, $listeners);
    }
}
