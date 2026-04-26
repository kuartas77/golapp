<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ResolvesLocalAssetPath
{
    protected function resolveLocalAssetPath(?string $value, array $fallbackCandidates): string
    {
        foreach ($this->localAssetPathCandidates($value) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        foreach ($fallbackCandidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $fallbackCandidates[0] ?? public_path('img/not-found.png');
    }

    protected function localAssetPathCandidates(?string $value): array
    {
        if (!is_string($value)) {
            return [];
        }

        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $candidates = [];

        if (Str::startsWith($value, [DIRECTORY_SEPARATOR, storage_path(), public_path(), base_path()])) {
            $candidates[] = $value;
        }

        $normalizedValue = ltrim($value, '/');

        if (Storage::disk('public')->exists($normalizedValue)) {
            $candidates[] = storage_path('app/public/' . $normalizedValue);
        }

        $path = parse_url($value, PHP_URL_PATH);

        if (is_string($path) && $path !== '') {
            $normalizedPath = ltrim($path, '/');

            if ($normalizedPath !== '') {
                if (Storage::disk('public')->exists($normalizedPath)) {
                    $candidates[] = storage_path('app/public/' . $normalizedPath);
                }

                if (Str::startsWith($normalizedPath, 'storage/')) {
                    $storageRelativePath = Str::after($normalizedPath, 'storage/');

                    if (Storage::disk('public')->exists($storageRelativePath)) {
                        $candidates[] = storage_path('app/public/' . $storageRelativePath);
                    }
                }

                if (preg_match('#^(?:api/)?img/dynamic/(.+)$#', $normalizedPath, $matches) === 1) {
                    $storageRelativePath = urldecode($matches[1]);

                    if (Storage::disk('public')->exists($storageRelativePath)) {
                        $candidates[] = storage_path('app/public/' . $storageRelativePath);
                    }
                }

                if (Str::startsWith($normalizedPath, 'public/')) {
                    $candidates[] = base_path($normalizedPath);
                }

                $candidates[] = public_path($normalizedPath);
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}
