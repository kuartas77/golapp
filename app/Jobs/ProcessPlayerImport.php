<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PlayerImport;
use App\Service\Import\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProcessPlayerImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(public int $playerImportId)
    {
        $this->onQueue('golapp_default');
    }

    public function handle(ImportService $service): void
    {
        $playerImport = PlayerImport::query()->findOrFail($this->playerImportId);

        if (in_array($playerImport->status, [PlayerImport::STATUS_COMPLETED, PlayerImport::STATUS_FAILED], true)) {
            return;
        }

        $playerImport->forceFill([
            'status' => PlayerImport::STATUS_PROCESSING,
            'error_message' => null,
            'started_at' => now(),
        ])->save();

        try {
            $absolutePath = Storage::disk($playerImport->disk)->path($playerImport->path);
            $file = new UploadedFile(
                path: $absolutePath,
                originalName: $playerImport->original_filename,
                test: true,
            );

            $summary = $service->players($file, (int) $playerImport->school_id);

            $playerImport->forceFill([
                'status' => PlayerImport::STATUS_COMPLETED,
                'summary' => $summary,
                'completed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $playerImport->forceFill([
                'status' => PlayerImport::STATUS_FAILED,
                'error_message' => $this->publicErrorMessage($exception),
                'completed_at' => now(),
            ])->save();

            report($exception);

            throw $exception;
        } finally {
            Storage::disk($playerImport->disk)->delete($playerImport->path);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $playerImport = PlayerImport::query()->find($this->playerImportId);

        if (! $playerImport || $playerImport->status === PlayerImport::STATUS_COMPLETED) {
            return;
        }

        $playerImport->forceFill([
            'status' => PlayerImport::STATUS_FAILED,
            'error_message' => $exception ? $this->publicErrorMessage($exception) : __('messages.error_general'),
            'completed_at' => now(),
        ])->save();

        Storage::disk($playerImport->disk)->delete($playerImport->path);
    }

    private function publicErrorMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return $exception->validator->errors()->first();
        }

        return Str::limit((string) __('messages.error_general'), 1000, '');
    }
}
