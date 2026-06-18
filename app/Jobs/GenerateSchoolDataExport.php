<?php

namespace App\Jobs;

use App\Models\SchoolDataExport;
use App\Service\SchoolDataExport\SchoolDataExportGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSchoolDataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(public int $schoolDataExportId)
    {
    }

    public function handle(SchoolDataExportGenerator $generator): void
    {
        $schoolDataExport = SchoolDataExport::query()->findOrFail($this->schoolDataExportId);

        $generator->generate($schoolDataExport);
    }
}
