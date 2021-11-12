<?php

namespace App\Traits;


trait ErrorTrait
{
    public function logError($message, $th)
    {
        logger()->error($message, [
            "error" => $th->getMessage(),
            "line" => $th->getLine(),
            "file" => $th->getFile(),
            "code" => $th->getCode(),
        ]);
    }
}
