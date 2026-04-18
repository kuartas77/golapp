<?php

namespace App\Exceptions;

use App\Traits\ErrorTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use ErrorTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->dontReportDuplicates();

        $this->reportable(function (Throwable $e) {
            if (! $this->shouldReport($e)) {
                return;
            }

            $this->logError(
                sprintf('Unhandled exception [%s]', class_basename($e)),
                $e,
                $this->notificationContext()
            );
        });
    }

    private function notificationContext(): array
    {
        if (app()->runningInConsole()) {
            return array_filter([
                'context' => 'console',
                'command' => implode(' ', $_SERVER['argv'] ?? []),
            ]);
        }

        if (! app()->bound('request')) {
            return [];
        }

        $request = request();

        return array_filter([
            'context' => 'http',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => optional($request->route())->getName(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ], static fn ($value) => ! is_null($value) && $value !== '');
    }
}
