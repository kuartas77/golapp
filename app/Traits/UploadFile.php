<?php

namespace App\Traits;

use App\Models\School;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

trait UploadFile
{
    public function saveFile(FormRequest $request)
    {
        
        $is_player = get_class($this) === "App\Repositories\PlayerRepository";
        
        $school = Str::slug("test example");
        $path = null;
        $folder =  $is_player ? $school . DIRECTORY_SEPARATOR ."players" : $school;

        if($request->hasFile('image'))
        {
            $file = $request->file('image');
            $path = $file->hashName($folder);

            $img = Image::make($file)->resize(420, 420, function ($constraint) {
                $constraint->aspectRatio();
            });
            Storage::disk('public')->put($path, (string)$img->encode());
        }
        return $path;
    }
}