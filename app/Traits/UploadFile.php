<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\File\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\School;

trait UploadFile
{
    public function saveFile(FormRequest $request, $field, bool $resize = true)
    {
        $path = null;

        if ($request->hasFile($field)) {

            $file = $request->file($field);
            $searchSchool = School::find($request->school_id);
            $school = ($searchSchool->slug ?? $request->slug);

            switch ($field) {
                case 'player':
                case 'photo':
                    $path = $file->hashName($school . DIRECTORY_SEPARATOR . "players");
                    break;
                case 'logo':
                default:
                    $path = $file->hashName($school);
                    break;
            }

            $img = Image::make($file);
            if($resize) {
                $img->resize(200, 200)->orientate();
            }

            Storage::disk('public')->put($path, (string)$img->encode('jpg', 75), 'public');
        }
        return $path;
    }

    public function uploadFile(UploadedFile $file, string $schoolFolder, string $field = 'players', bool $resize = true): ?string
    {
        $path = null;

        $folder = $field;

        $path = $file->hashName("{$schoolFolder}/{$folder}");

        $img = Image::make($file);
        if($resize) {
            $img->resize(200, 200);
        }

        Storage::disk('public')->put($path, (string)$img->encode(), 'public');

        return $path;
    }

    private function uploadSignImage($data, $folder = 'player', bool $resize = true)
    {
        $parts = explode(',', (string) $data, 2);
        $decoded_image = isset($parts[1]) ? base64_decode($parts[1], true) : false;

        if ($decoded_image === false || strlen($decoded_image) > 1048576) {
            throw new \InvalidArgumentException('Invalid signature image.');
        }

        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();

        try {
            if (file_put_contents($tmpFilePath, $decoded_image) === false) {
                throw new \RuntimeException('Unable to create signature image.');
            }

            $tmpFile = new File($tmpFilePath);
            $file = new UploadedFile(
                $tmpFile->getPathname(),
                $tmpFile->getFilename(),
                $tmpFile->getMimeType(),
                0,
                false
            );
            $path = $file->hashName($folder);
            $img = Image::make($file);

            if ($resize) {
                $img->resize(200, 200)->orientate();
            }

            Storage::disk('local')->put($path, (string) $img->encode(), 'public');

            return $path;
        } finally {
            if (is_file($tmpFilePath)) {
                unlink($tmpFilePath);
            }
        }
    }
}
