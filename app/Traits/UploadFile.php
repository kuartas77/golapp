<?php

namespace App\Traits;

use App\Models\School;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

trait UploadFile
{
    public function saveFile(FormRequest $request, $field)
    {        
        $path = null;

        if($request->hasFile($field)) {
            
            $file = $request->file($field);
            $school = School::find($request->school_id)->slug;

            switch ($field) {
                case 'player':
                    $path = $file->hashName($school . DIRECTORY_SEPARATOR ."players");
                    break;
                case 'logo':
                default:
                    $path = $file->hashName($school);
                    break;
            }
            
            $img = Image::make($file)->resize(420, 420, function ($constraint) {
                $constraint->aspectRatio();
            });
            Storage::disk('public')->put($path, (string)$img->encode());
        }
        return $path;
    }
}