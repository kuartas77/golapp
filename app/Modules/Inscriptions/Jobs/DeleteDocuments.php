<?php

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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
     *
     * @return void
     */
    public function handle()
    {
        try {

            Storage::disk('public')->deleteDirectory($this->folder.DIRECTORY_SEPARATOR.$this->uniqueCode);

        } catch (\Throwable $th) {
            $this->logError(__FUNCTION__, $th);
        }
    }
}
