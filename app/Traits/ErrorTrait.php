<?php

namespace App\Traits;

use App\Mail\ErrorLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;


trait ErrorTrait
{
    public function logError(string $message, Throwable $th, array $extraContext = []): void
    {
        $context = array_merge([
            "error" => $th->getMessage(),
            "line" => $th->getLine(),
            "file" => $th->getFile(),
            "code" => $th->getCode(),
            "exception" => get_class($th),
        ], $extraContext);

        try {
            logger()->error($message, $context);
        } catch (Throwable $loggingException) {
            error_log(sprintf(
                'ErrorTrait logger failure [%s]: %s. Original error: %s',
                $message,
                $loggingException->getMessage(),
                $th->getMessage()
            ));
        }

        try {
            $emails = User::query()
                ->role('super-admin')
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($emails)) {
                return;
            }

            Mail::to($emails)->send(new ErrorLog($message, $context));
        } catch (Throwable $notificationException) {
            try {
                logger()->error('ErrorTrait notification failure', [
                    'message' => $message,
                    'notification_error' => $notificationException->getMessage(),
                    'notification_file' => $notificationException->getFile(),
                    'notification_line' => $notificationException->getLine(),
                ]);
            } catch (Throwable) {
                error_log(sprintf(
                    'ErrorTrait notification failure [%s]: %s',
                    $message,
                    $notificationException->getMessage()
                ));
            }
        }
    }
}
