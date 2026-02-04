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
    public function saveFile(FormRequest $request, $field)
    {
        $path = null;

        if ($request->hasFile($field)) {

            $file = $request->file($field);
            $searchSchool = School::find($request->school_id);
            $school = ($searchSchool->slug ?? $request->slug);

            switch ($field) {
                case 'player':
                    $path = $file->hashName($school . DIRECTORY_SEPARATOR . "players");
                    break;
                case 'logo':
                default:
                    $path = $file->hashName($school);
                    break;
            }

            $img = Image::make($file)->resize(200, 200)->orientate();
            Storage::disk('public')->put($path, (string)$img->encode('jpg', 75), 'public');
        }
        return $path;
    }

    public function uploadFile(UploadedFile $file, string $schoolFolder, string $field = 'player'): ?string
    {
        $path = null;

        $folder = $field === 'player' ? 'players' : 'profiles';

        $path = $file->hashName("{$schoolFolder}/{$folder}");

        $img = Image::make($file)->resize(200, 200);
        Storage::disk('public')->put($path, (string)$img->encode(), 'public');

        return $path;
    }

    private function uploadSignImage($data, $folder = 'player')
    {
        $path = null;
        $encoded_image = explode(",", $data)[1];
        $decoded_image = base64_decode($encoded_image);
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        file_put_contents($tmpFilePath, $decoded_image);

        // this just to help us get file info.
        $tmpFile = new File($tmpFilePath);

        $file = new UploadedFile(
            $tmpFile->getPathname(),
            $tmpFile->getFilename(),
            $tmpFile->getMimeType(),
            0,
            false
        );

        $path = $file->hashName($folder);

        $img = Image::make($file)->resize(200, 200);

        Storage::disk('local')->put($path, (string)$img->encode(), 'public');

        return $path;
    }
}
