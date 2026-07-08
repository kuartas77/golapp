<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

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

        $exitCode = $this->call('optimize');

        if ($exitCode !== self::SUCCESS || ! $this->frameworkCachesExist()) {
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

        return $this->laravel->configurationIsCached()
            && $this->laravel->eventsAreCached()
            && $this->laravel->routesAreCached()
            && is_string($compiledViewsPath)
            && File::isDirectory($compiledViewsPath);
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
