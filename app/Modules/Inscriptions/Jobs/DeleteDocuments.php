<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Jobs;

use App\Traits\ErrorTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeleteDocuments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ErrorTrait;

    /**
     * Create a new job instance.
     *
     * @return void
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
            $this->logError(__FUNCTION__, $throwable);
        }
    }
}
