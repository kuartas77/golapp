<?php

namespace App\Jobs;

use App\Service\SchoolDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteSchoolPermanently implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(public int $schoolId) {}

    public function handle(SchoolDeletionService $service): void
    {
        $service->delete($this->schoolId);
    }
}
