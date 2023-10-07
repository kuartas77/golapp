<?php

namespace App\Traits;

use App\Mail\ErrorLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;


trait ErrorTrait
{
    public function logError($message, $th): void
    {
        $context = [
            "error" => $th->getMessage(),
            "line" => $th->getLine(),
            "file" => $th->getFile(),
            "code" => $th->getCode(),
        ];

        logger()->error($message, $context);

        $users = User::role('super-admin')->get();

        Mail::to($users)->send(new ErrorLog($message, $context));
    }
}
