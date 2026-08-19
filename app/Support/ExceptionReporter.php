<?php

declare(strict_types=1);

namespace App\Support;

use App\Traits\ErrorTrait;
use Throwable;

final class ExceptionReporter
{
    use ErrorTrait;

    public function report(Throwable $exception): void
    {
        $this->logError(
            sprintf('Unhandled exception [%s]', class_basename($exception)),
            $exception,
            $this->notificationContext()
        );
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
