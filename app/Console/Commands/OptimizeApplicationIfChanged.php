<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class OptimizeApplicationIfChanged extends Command
{
    protected $signature = 'optimize:if-changed';

    protected $description = 'Optimize the application only when its cache sources have changed';

    public function handle(): int
    {
        $fingerprint = $this->cacheSourcesFingerprint();
        $fingerprintPath = storage_path('framework/cache/optimize-sources.sha256');

        if ($this->optimizationCachesExist()
            && File::exists($fingerprintPath)
            && trim(File::get($fingerprintPath)) === $fingerprint) {
            $this->components->info('La optimización de Laravel ya está actualizada.');

            return self::SUCCESS;
        }

        if (! $this->runOptimizationCommands() || ! $this->frameworkCachesExist()) {
            $this->components->error('No fue posible generar todas las cachés de optimización.');

            return self::FAILURE;
        }

        File::put($this->viewCacheMarkerPath(), $fingerprint.PHP_EOL);
        File::ensureDirectoryExists(dirname($fingerprintPath));
        File::put($fingerprintPath, $fingerprint.PHP_EOL);

        $this->components->info('La optimización de Laravel fue actualizada.');

        return self::SUCCESS;
    }

    private function optimizationCachesExist(): bool
    {
        return $this->frameworkCachesExist()
            && File::exists($this->viewCacheMarkerPath());
    }

    private function frameworkCachesExist(): bool
    {
        $compiledViewsPath = config('view.compiled');

        return File::exists($this->laravel->getCachedConfigPath())
            && File::exists($this->laravel->getCachedEventsPath())
            && File::exists($this->laravel->getCachedRoutesPath())
            && is_string($compiledViewsPath)
            && File::isDirectory($compiledViewsPath);
    }

    private function runOptimizationCommands(): bool
    {
        $this->components->info('Actualizando cachés de optimización de Laravel.');

        foreach ($this->optimizationCommands() as $command) {
            try {
                $exitCode = Artisan::call($command);
                $output = trim(Artisan::output());
            } catch (Throwable $exception) {
                Log::error('Falló un subcomando de optimize:if-changed.', [
                    'command' => $command,
                    'exception' => $exception,
                ]);

                $this->components->error("Falló el subcomando [{$command}].");

                return false;
            }

            if ($exitCode !== self::SUCCESS) {
                Log::error('Un subcomando de optimize:if-changed terminó con error.', [
                    'command' => $command,
                    'exit_code' => $exitCode,
                    'output' => $output,
                ]);

                $this->components->error("El subcomando [{$command}] terminó con código {$exitCode}.");

                return false;
            }
        }

        return true;
    }

    private function optimizationCommands(): array
    {
        return [
            'config:cache',
            'event:cache',
            'route:cache',
            'view:cache',
            ...array_values(ServiceProvider::$optimizeCommands),
        ];
    }

    private function viewCacheMarkerPath(): string
    {
        return rtrim(config('view.compiled'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'.optimized-by-scheduler';
    }

    private function cacheSourcesFingerprint(): string
    {
        $context = hash_init('sha256');

        foreach ($this->cacheSourceFiles() as $path) {
            hash_update($context, str_replace(base_path().DIRECTORY_SEPARATOR, '', $path));
            hash_update($context, File::get($path));
        }

        return hash_final($context);
    }

    private function cacheSourceFiles(): Collection
    {
        $files = collect([
            base_path('.env'),
            base_path('composer.lock'),
            base_path('bootstrap/app.php'),
        ]);

        foreach (['config', 'routes', 'resources/views', 'app/Providers', 'app/Events', 'app/Listeners'] as $directory) {
            $path = base_path($directory);

            if (File::isDirectory($path)) {
                $files->push(...collect(File::allFiles($path))->map(
                    fn (\SplFileInfo $file): string => $file->getPathname()
                ));
            }
        }

        return $files
            ->filter(fn (string $path): bool => File::isFile($path))
            ->unique()
            ->sort()
            ->values();
    }
}
