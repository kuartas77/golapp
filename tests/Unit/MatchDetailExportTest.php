<?php

namespace Tests\Unit;

use App\Exports\MatchDetailExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\TestCase;

class MatchDetailExportTest extends TestCase
{
    public function test_match_detail_download_is_not_queued(): void
    {
        $export = new MatchDetailExport(match: 6);

        $this->assertNotInstanceOf(ShouldQueue::class, $export);
    }
}
