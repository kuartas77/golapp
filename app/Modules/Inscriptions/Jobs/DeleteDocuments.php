<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteDocuments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $folder, private string $uniqueCode)
    {
        $this->afterCommit();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            Storage::disk('public')->deleteDirectory($this->folder.DIRECTORY_SEPARATOR.$this->uniqueCode);

        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }
}
