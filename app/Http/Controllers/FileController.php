<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function fileStorageServe($file): Response|Application|ResponseFactory
    {
        try {
            $file_path = explode('?', $file)[0];

            if (!Storage::disk('public')->exists($file_path)) {
                return response(file_get_contents(public_path('img/not-found.png')))->withHeaders([
                    'Content-Type' => "image/png",
                    'Cache-Control' => 'public, no-transform, max-age=31536000'
                ]);
            }

            return response(Storage::disk('public')->get($file_path))->withHeaders([
                'Content-Type' => Storage::disk('public')->mimeType($file_path),
                'Cache-Control' => 'public, no-transform, max-age=31536000'
            ]);
        } catch (\Exception $e) {
            return response(null, 404);
        }
    }
}
