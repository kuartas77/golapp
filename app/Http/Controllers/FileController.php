<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function fileStorageServe($file): Response|Application|ResponseFactory
    {
        try {
            $file_path = ltrim(str_replace('\\', '/', explode('?', $file)[0]), '/');

            if (!$this->isAllowedPublicImagePath($file_path)) {
                return response(null, 404);
            }

            if (!Storage::disk('public')->exists($file_path)) {
                return response(file_get_contents(public_path('img/not-found.png')))->withHeaders([
                    'Content-Type' => "image/png",
                    'Cache-Control' => 'public, no-transform, max-age=31536000'
                ]);
            }

            $mimeType = Storage::disk('public')->mimeType($file_path);

            if (!is_string($mimeType) || !str_starts_with($mimeType, 'image/')) {
                return response(null, 404);
            }

            return response(Storage::disk('public')->get($file_path))->withHeaders([
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, no-transform, max-age=31536000'
            ]);
        } catch (\Exception $e) {
            report($e);

            return response(null, 404);
        }
    }

    private function isAllowedPublicImagePath(string $filePath): bool
    {
        if ($filePath === '' || str_contains($filePath, '..')) {
            return false;
        }

        return preg_match('#^[^/]+/players/[^/]+$#', $filePath) === 1
            || preg_match('#^[^/]+/[^/]+$#', $filePath) === 1;
    }
}
